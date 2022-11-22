<?php

namespace App;

class Symbol
{
    private string $title;
    private int $frequency;
    private array $prices;

    public function __construct(string $title, int $frequency, array $prices)
    {
        $this->title = $title;
        $this->frequency = $frequency;
        $this->prices = $prices;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function getFrequency(): int
    {
        return $this->frequency;
    }


    public function getPrices(): array
    {
        return $this->prices;
    }

}