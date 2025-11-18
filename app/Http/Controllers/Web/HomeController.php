<?php

namespace App\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\SystemController;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Models\CloudShare;

class HomeController extends Controller
{
    /**
     * Get the post form the posts folder.
     */
    private function getStories($max = 100)
    {
        $storiesFolder = 'x-stories';
        $stories = [];

        if (!is_dir(public_path($storiesFolder))) {
            return [];
        }

        $folders = scandir(public_path($storiesFolder));

        // Sort the folders by date.
        usort($folders, function ($a, $b) use ($storiesFolder) {
            $storyJsonA = public_path($storiesFolder . '/' . $a . '/story.json');
            $storyJsonB = public_path($storiesFolder . '/' . $b . '/story.json');

            if (!file_exists($storyJsonA) || !file_exists($storyJsonB)) {
                return 0;
            }

            $jsonA = json_decode(file_get_contents($storyJsonA), true);
            $jsonB = json_decode(file_get_contents($storyJsonB), true);

            return strtotime($jsonB['date']) - strtotime($jsonA['date']);
        });

        foreach ($folders as $folder) {
            if (count($stories) === $max) {
                break;
            }

            if ($folder === '.' || $folder === '..') {
                continue;
            }

            $storyJson = public_path($storiesFolder . '/' . $folder . '/story.json');

            if (!file_exists($storyJson)) {
                continue;
            }

            $json = json_decode(file_get_contents($storyJson), true);
            $alt = $json['alt'] ?? $json['title'];

            $stories[] = [
                'title' => $json['title'],
                'description' => $json['description'] ?? '',
                'date' => $json['date'],
                'alt' => $alt,
                'author' => $json['author'],
                'thumbnail' => url('/') . '/x-stories/' . $folder . '/' . $json['thumbnail'],
                'permalink' => url('/') . '/stories/' . $json['slug'],
                'slug' => $json['slug'],
            ];
        }

        return $stories;
    }

    /**
     * Show the application welcome screen.
     */
    public function stories()
    {
        return view('stories.entities', [
            'stories' => $this->getStories(),
        ]);
    }

    /**
     * Show the application welcome screen.
     */
    public function story($slug)
    {
        $mapper = public_path('x-stories/stories-slug-mapper.txt');
        if (file_exists($mapper)) {
            $lines = file($mapper);
            foreach ($lines as $line) {
                $parts = explode(':', $line);
                $parts[1] = trim($parts[1]);
                if ($parts[1] === $slug) {
                    $slug = $parts[0];
                    break;
                }
            }
        }

        $storiesFolder = 'x-stories';
        $storyJson = public_path($storiesFolder . '/' . $slug . '/story.json');

        if (!file_exists($storyJson)) {
            abort(404);
        }

        $json = json_decode(file_get_contents($storyJson), true);
        $markdown = file_get_contents(public_path($storiesFolder . '/' . $slug . '/story.md'));
        $markdown = str_replace('{{image-path}}', url('/') . '/x-stories/' . $slug . '/images', $markdown);
        $markdown = str_replace('{{video-path}}', url('/') . '/x-stories/' . $slug . '/videos', $markdown);
        return view('stories.entity', [
            'title' => $json['title'],
            'date' => $json['date'],
            'author' => $json['author'],
            'thumbnail' => $json['thumbnail'],
            'content' => Str::markdown($markdown),
        ]);
    }

    /**
     * Download the latest releases.
     */
    public function download(Request $request)
    {
        $platform = $request->platform;
        $architecture = $request->architecture;
        if ($platform === 'win') {
            return redirect('https://apps.microsoft.com/detail/9mtw32cfv272');
        }

        if ($platform === 'mac') {
            return redirect('https://x-releases.s3.eu-west-2.amazonaws.com/macOS/' . $architecture . '/Classer.dmg');
        }
    }

    /**
     * Show the application welcome screen.
     */
    public function index()
    {
        return view('welcome', [
            'stories' => $this->getStories(6),
        ]);
    }

    /**
     * Show the application subscriptions page.
     */
    public function subscriptions(Request $request)
    {

        $token = $request->query('t');
        $payload = $request->session()->get('payload');

        $accessToken = PersonalAccessToken::findToken($token);
        $user = $accessToken?->tokenable;

        // Load and merge subscription data
        $systemController = new SystemController();
        $resourceSubscriptions = collect($systemController->loadFromResource('subscriptions.dataset.json'));
        $dbSubscriptions = Subscription::all()->keyBy('code');

        $subscriptions = $resourceSubscriptions->map(function ($item) use ($dbSubscriptions) {
            $match = $dbSubscriptions->get($item['code']);
            $item['subscription_id'] = $match?->uid;
            return $item;
        });

        $selectedPlan = null;
        if ($user && is_array($payload)) {
            $planCode = $payload['code'] ?? null;

            if (!$planCode) {
                return Log::warning('Missing plan in payload', ['payload' => $payload]);
            }

            $selectedPlan = $subscriptions->firstWhere('code', $planCode);

            if (!$selectedPlan) {
                return Log::warning('Invalid plan code in payload', ['code' => $planCode]);
            }

            if (!$selectedPlan['subscription_id']) {
                return Log::warning('Missing subscription_id for plan', ['code' => $selectedPlan]);
            }

            $this->createSeededSubscription($user, $selectedPlan['subscription_id']);
        }

        return view('subscriptions', [
            'subscriptions' => $subscriptions,
            'subscription' => $selectedPlan,
            'openApp' => $accessToken ? 'classer://auth/login?token=' . $token : null,
        ]);
    }

    /**
     * Create a seeded subscription for testing purposes.
     */
    protected function createSeededSubscription($user, $subscriptionId): void
    {
        DB::transaction(function () use ($user, $subscriptionId) {
            $paymentMethod = PaymentMethod::create([
                'uid' => Str::uuid(),
                'user_id' => $user->uid,
                'provider' => 'stripe',
                'type' => 'service',
                'stripe_customer_id' => 'cus_' . Str::random(16),
                'stripe_payment_method_id' => 'pm_' . Str::random(16),
                'stripe_transaction_id' => 'tr_' . Str::random(16),
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ]);

            UserSubscription::create([
                'uid' => Str::uuid(),
                'user_id' => $user->uid,
                'subscription_id' => $subscriptionId,
                'payment_method_id' => $paymentMethod->uid,
                'status' => 'active',
                'auto_renew' => true,
                'expiration_date' => now()->addMonths(6),
                'auto_renew_date' => now()->addMonths(6),
                'transaction_id' => 'pi_' . Str::random(16),
                'updated_by' => 'system',
                'notes' => 'Seeded subscription for testing',
            ]);
        });
    }

    /**
     *  Handle the selection of a subscription plan.
     */
    public function handleSelection(Request $request)
    {
        $selectedPlan = $request->input('plan');
        $payload = [
            'plan' => $selectedPlan,
            'timestamp' => time(),
        ];

        return redirect()->back()->with('payload', $payload);
    }

    /**
     * Action camera matcher.
     */
    public function actionCameraMatcher(Request $request)
    {
        $segment = last(explode('/', $request->path()));

        if ($segment === 'action-camera-matcher') {
            return view('action-camera-matcher/index/index', [
                'stories' => $this->getStories(3),
            ]);
        }

        if ($segment === 'questions') {
            return view('action-camera-matcher/questions/questions', [
                'questionnaire' => app(SystemController::class)
                    ->loadFromResource('action-camera-questionnaire.dataset.json'),
            ]);
        }

        return abort(404);
    }


    /**
     * Action camera matcher results.
     * 
     * @param Request $request
     * @param string $answers Base64 encoded JSON string of answers
     */
    function actionCameraMatcherResults(Request $request, $answers)
    {
        $decodedAnswers = json_decode(base64_decode($answers), true);
        $questionnaire = app(SystemController::class)
            ->loadFromResource('action-camera-questionnaire.dataset.json');

        // vallidate answers by checking its an array and has the same number of entries as questions
        if (!is_array($decodedAnswers) || count($decodedAnswers) !== count($questionnaire['questions'])) {  
            return redirect('/action-camera-matcher/questions');
        }

        $cameraWeights = $questionnaire['weights'];
        $cameraBenefits = $questionnaire['benefits'];
        $cameraAffiliateLinks = $questionnaire['affiliateLink'];
        return view('action-camera-matcher/results/results', [
            'stories' => $this->getStories(3),
            'answers' => $decodedAnswers,
            'recommendations' => $this->getResults(
                $cameraWeights,
                $cameraBenefits,
                $cameraAffiliateLinks,
                $decodedAnswers
            ),
        ]);
    }

    /**
     * Get the results based on the weights and answers
     * 
     * @param array $weights Array of [name => itemWeights] pairs
     * @param array $benefits Array of benefits for each camera
     * @param array $answers Array of answer indices
     * @return array Sorted results with percentages and recommendations
     */
    function getResults(
        array $weights,
        array $benefits,
        array $affiliateLinks,
        array $answers
    ): array {
        $weightAnswerMap = array_map(function ($cameraQuestionWeights) use ($answers) {
            $answersForCamera = [];
            foreach ($answers as $answerIndex => $answerValue) {
                $answersForCamera[] = $cameraQuestionWeights[$answerIndex][$answerValue] ?? null;
            }
            return $answersForCamera;
        }, $weights);

        // Filter out cameras that have any "out" answers
        $weightAnswerMap = array_filter($weightAnswerMap, function ($answers) {
            return !in_array('out', $answers, true);
        });

        // lets sume up the weights for each camera
        $cameraScores = array_map(function ($answers) {
            return array_sum($answers);
        }, $weightAnswerMap);

        // lets sort the scores in descending order
        arsort($cameraScores);

        // hightest score
        $maxScore = max($cameraScores);

        // build results array
        return array_map(function ($model, $score) use ($maxScore, $benefits, $affiliateLinks) {    
            $percentage = ($score / $maxScore) * 100;
            return [
                'title' => $model,
                'key' => Str::slug($model),
                'score' => $score,
                'percentage' => round($percentage, 2),
                'recommendation_key' => $this->getRecommendationKey($percentage),
                'recommendation' => $this->getRecommendation($percentage),
                'image' => asset('/assets/images/action-camera-matcher/cameras/' . $model . '.jpg'),
                'affiliateLink' => $affiliateLinks[$model] ?? null,
                'benefits' => $benefits[$model] ?? null,
            ];
        }, array_keys($cameraScores), $cameraScores);
    }

    /**
     * Get the recommendation key based on the percentage
     * 
     * @param float $percentage
     * @return string
     */
    function getRecommendationKey(float $percentage): string
    {
        return match (true) {
            $percentage > 90 => 'highly-recommended',
            $percentage > 70 => 'good-match',
            default => 'might-like',
        };
    }

    /**
     * Get the recommendation based on the percentage
     * 
     * @param float $percentage
     * @return string
     */
    function getRecommendation(float $percentage): string
    {
        return match (true) {
            $percentage > 90 => 'Highly recommend!',
            $percentage > 70 => "It's a good match!",
            default => 'You might like it!',
        };
    }

    /**
     * Privacy policy.
     */
    public function privacyPolicy($isoLanCode)
    {
        $privacyPolicy = public_path('privacy-policy/' . $isoLanCode . '.md');

        if (!file_exists($privacyPolicy)) {
            abort(404);
        }

        $markdown = file_get_contents($privacyPolicy);
        return view('privacy-policy', [
            'content' => Str::markdown($markdown),
        ]);
    }

    /**
     * How to deactivate.
     */
    public function howToDeactivate()
    {
        $privacyPolicy = public_path('privacy-policy/' . 'en-gb' . '.md');

        if (!file_exists($privacyPolicy)) {
            abort(404);
        }

        $markdown = file_get_contents($privacyPolicy);
        return view('privacy-policy', [
            'content' => Str::markdown($markdown),
        ]);
    }

    /**
     * How to deactivate.
     */
    public function shareMoment($uid)
    {
        $entity = CloudShare::where('uid', $uid)->firstOrFail();
        $cloudEntities = $entity->cloudEntities;
        $video = collect($cloudEntities)->firstWhere('type', 'video/mp4');
        $thumbnail = collect($cloudEntities)->firstWhere('type', 'image/jpeg');
        return view('share-moment', [
            'videoSrc' => $video->public_url,
            'thumbnailSrc' => $thumbnail->public_url,
            'entities' => $cloudEntities,
        ]);
    }
}
