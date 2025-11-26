<?php

namespace App\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\SystemController;

class ActionCameraMatcherController extends Controller
{
    /**
     * Action camera matcher index page.
     */
    public function index(Request $request)
    {
        return view('action-camera-matcher/index/index', [
            'posts' => $this->getPosts('blog', 4),
        ]);
    }

    /**
     * Action camera matcher questions page.
     */
    public function questions(Request $request)
    {
        return view('action-camera-matcher/questions/questions', [
            'questionnaire' => app(SystemController::class)
                ->loadFromResource('action-camera-questionnaire.dataset.json'),
        ]);
    }

    /**
     * Action camera matcher results.
     * 
     * @param Request $request
     * @param string $answers Base64 encoded JSON string of answers
     */
    function results(Request $request, $answers)
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
}
