<?php

namespace App;

class SymbolCollection
{
    private array $symbols;

    public function __construct(array $symbols = [])
    {
        foreach ($symbols as $symbol) {
            $this->add($symbol);
        }
    }

    public function add(Symbol $symbol)
    {
        $this->symbols[] = $symbol;
    }

    public function getAll(): array
    {
        return $this->symbols;
    }

    public function getSymbolByTitle(string $title): ?Symbol
    {
        foreach ($this->symbols as $symbol) {
            if ($title === $symbol->getTitle()) {
                return $symbol;
            }
        }
        return null;
    }

    public function getRandomSymbol(): Symbol
    {
        $randomSymbols = [];
        /** @var Symbol $symbol */
        foreach ($this->symbols as $symbol) {
            for ($i = 0; $i < $symbol->getFrequency(); $i++) {
                $randomSymbols[] = $symbol;
            }
        }
        shuffle($randomSymbols);

        //don't like random_int(), maybe will use it in real projects
        //read the docs about it, it states than it is used for gambling random generation
        //you can always uncomment the lines below
        /*try {
            $random = random_int(0, count($randomSymbols)-1);
            return($randomSymbols[$random]);
        } catch (Exception $e) {
            var_dump($e);
        }*/
        return $randomSymbols[array_rand($randomSymbols)];
    }
}