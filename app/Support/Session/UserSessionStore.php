<?php

namespace App\Support\Session;

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Arr;

class UserSessionStore
{
    private const ROOT_KEY = 'user_context';

    public function __construct(private readonly Store $session) {}

    public static function fromRequest(Request $request): self
    {
        return new self($request->session());
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $data = $this->session->get(self::ROOT_KEY, []);

        return is_array($data) ? $data : [];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->all(), $key);
    }

    public function put(string $key, mixed $value): void
    {
        $data = $this->all();
        Arr::set($data, $key, $value);
        $this->session->put(self::ROOT_KEY, $data);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function putMany(array $values): void
    {
        $data = $this->all();

        foreach ($values as $key => $value) {
            Arr::set($data, $key, $value);
        }

        $this->session->put(self::ROOT_KEY, $data);
    }

    public function forget(string $key): void
    {
        $data = $this->all();
        Arr::forget($data, $key);
        $this->session->put(self::ROOT_KEY, $data);
    }

    public function clear(): void
    {
        $this->session->forget(self::ROOT_KEY);
    }
}