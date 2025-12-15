<?php

namespace App\Abstractions;

abstract class Service
{
    private array $inputs = [];
    private array $outputs = [];

    public function setInputs(array $inputs): static
    {
        $this->inputs = $inputs;
        return $this;
    }

    public function setInput(string $key, mixed $value): static
    {
        $this->inputs[$key] = $value;
        return $this;
    }

    public function collectOutputs(&$outputs): static
    {
        $outputs = $this->outputs;
        return $this;
    }

    public function collectOutput(string $key, mixed &$output): static
    {
        $output = $this->outputs[$key] ?? null;
        return $this;
    }

    protected function setOutputs(array $outputs): void
    {
        $this->outputs = array_merge($this->outputs, $outputs);
    }

    protected function setOutput(string $key, mixed $value): static
    {
        $this->outputs[$key] = $value;
        return $this;
    }

    protected function getInput(string $key, mixed $default = null): mixed
    {
        return $this->inputs[$key] ?? $default;
    }

    protected function getInputs(): array
    {
        return $this->inputs;
    }
}
