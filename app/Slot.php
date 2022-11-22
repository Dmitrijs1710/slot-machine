<?php

namespace App;

class Slot
{
    private array $field;
    private array $combinations;

    private int $lines;
    private int $multiplier;
    private SymbolCollection $symbols;
    private ?Symbol $freespinSymbol;
    private int $freespins;
    private array $bonusWildPayTable;
    private int $balance;
    private bool $bonus = false;
    private int $lastWin;
    private Symbol $bonusWild;

    public function __construct(
        array            $field,
        array            $combinations,
        array            $bonusWildPayTable,
        int              $balance,
        SymbolCollection $symbols,
        Symbol           $bonusWild,
        Symbol           $freespinSymbol = null,
        int              $freespins = 0,
        int              $lines = 10,
        int              $multiplier = 1,
        int              $lastWin = 0
    )
    {
        $this->field = $field;
        $this->combinations = $combinations;
        $this->lines = $lines;
        $this->multiplier = $multiplier;
        $this->symbols = $symbols;
        $this->freespinSymbol = $freespinSymbol;
        $this->freespins = $freespins;
        $this->bonusWildPayTable = $bonusWildPayTable;
        $this->balance = $balance;
        $this->lastWin = $lastWin;
        $this->bonusWild = $bonusWild;
    }


    public function getLines(): int
    {
        return $this->lines;
    }



    public function getField(): array
    {
        return $this->field;
    }


    public function getCombinations(): array
    {
        return $this->combinations;
    }


    public function getMultiplier(): int
    {
        return $this->multiplier;
    }

    public function getLastWin(): int
    {
        return $this->lastWin;
    }

    public function getBonusWild(): Symbol
    {
        return $this->bonusWild;
    }

    public function randomField(): void
    {
        //every row in a column must be unique
        for ($x = 0; $x < count($this->field[0]); $x++) {
            for ($y = 0; $y < count($this->field); $y++) {
                while (true) {
                    $this->field[$y][$x] = $this->symbols->getRandomSymbol();
                    $randBool = true;
                    for ($i = 0; $i < $y; $i++) {
                        if ($this->field[$i][$x] === $this->field[$y][$x]) {
                            $randBool = false;
                            break;
                        }
                    }
                    if ($randBool) {
                        break;
                    }
                }

            }
        }
    }

    public function getSymbols(): array
    {
        return $this->symbols->getAll();
    }


    public function getFreespins(): int
    {
        return $this->freespins;
    }


    public function getFreespinSymbol(): Symbol
    {
        return $this->freespinSymbol;
    }


    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getStake(): int
    {
        return $this->getLines() * $this->getMultiplier();
    }

    public function getBonusWildPayTable(): array
    {
        return $this->bonusWildPayTable;
    }

    public function getWinAmount(string $exception = null, bool $expand = false): int
    {
        $winAmount = 0;
        if (!$expand) {
            foreach ($this->getCombinations() as $key => $combination) {
                if ($key === $this->lines) {
                    break;
                }
                $count = 0;
                [$x, $y] = $combination[0];
                $symbol = $this->field[$y][$x];
                //B is the Wild;
                if ($symbol->getTitle() === $this->bonusWild->getTitle()) {
                    foreach ($combination as $coordinates) {
                        [$x, $y] = $coordinates;
                        if (($this->field[$y][$x]->getTitle() === $symbol->getTitle() || $this->field[$y][$x]->getTitle() === $this->bonusWild->getTitle()) && $this->field[$y][$x]->getTitle() !== $exception) {
                            $count++;
                        } else break;
                    }
                    if ($count > 1) {
                        $winAmount += $symbol->getPrices()[$count - 2];
                    }
                } else {
                    $maxWin = 0;
                    foreach ($combination as $coordinates) {
                        [$x, $y] = $coordinates;
                        if ($symbol === 'B' && $this->field[$y][$x] !== 'B') {
                            $symbol = $this->field[$y][$x];
                        }
                        if (($this->field[$y][$x] === $symbol || $this->field[$y][$x] === 'B') && $this->field[$y][$x] !== $exception) {
                            $count++;
                            if ($count > 1) {
                                $maxWin = max($symbol->getPrices()[$count - 2], $maxWin);
                            }
                        } else break;
                    }
                    if ($count > 1) {
                        $winAmount += max($symbol->getPrices()[$count - 2], $maxWin);
                    } else $winAmount += $maxWin;

                }
            }
        } else {
            $field = $this->getExpandBonusField();
            foreach ($this->getCombinations() as $combination) {
                $count = 0;
                foreach ($combination as $coordinates) {
                    [$x, $y] = $coordinates;
                    if ($field[$y][$x]->getTitle() === $exception) {
                        $count++;
                    }
                }
                if ($count > 1) {
                    $winAmount += $this->symbols->getSymbolByTitle($exception)->getPrices()[$count - 2];
                }
            }
        }
        return ($winAmount);
    }

    public function deposit(int $value): void
    {
        if ($value > 0) {
            $this->balance += $value;
        }
    }

    public function withdraw(int $value): void
    {
        if ($this->balance - $value >= 0) {
            $this->balance -= $value;
        }
    }

    public function setLines(int $lines): void
    {
        if ($lines > 0 && $lines <= 10) {
            $this->lines = $lines;
        }
    }


    public function setMultiplier(int $multiplier): void
    {
        if ($multiplier > 0) {
            $this->multiplier = $multiplier;
        }
    }


    public function isBonus(): bool
    {
        return $this->bonus;
    }

    public function getExpandBonusField(): array
    {
        $field = $this->field;
        for ($x = 0; $x < 5; $x++) {
            for ($y = 0; $y < 3; $y++) {
                if ($field[$y][$x] === $this->freespinSymbol) {
                    for ($g = 0; $g < 3; $g++) {
                        $field[$g][$x] = $this->freespinSymbol;
                    }
                    break;
                }
            }
        }
        return $field;
    }

    public function Spin(): void
    {
        $this->lastWin = 0;
        if ($this->getFreespins() === 0) {
            $this->freespinSymbol = null;
            $this->bonus = false;
            $this->withdraw($this->getStake());
        } else {
            $this->bonus=true;
            $this->freespins--;
        }
        $this->randomField();
        if ($this->bonus) {
            $this->lastWin = $this->multiplier * $this->getWinAmount($this->getFreespinSymbol()->getTitle());
        } else $this->lastWin = $this->multiplier * $this->getWinAmount();
        //3+ books check
        $count = 0;
        foreach ($this->field as $row) {
            foreach ($row as $column) {
                if ($column->getTitle() === $this->bonusWild->getTitle()) {
                    $count++;
                }
            }
        }
        if ($count > 2) {
            $this->lastWin += $this->getStake() * $this->bonusWildPayTable[$count - 3]; //adds books amount
            if ($this->freespins === 0 && !$this->bonus) {
                while (true) {
                    $this->freespinSymbol = $this->symbols->getRandomSymbol();
                    if ($this->freespinSymbol->getTitle() !== $this->bonusWild->getTitle()) break; //bonusWild can't be bonus symbol
                }
            }
            $this->freespins += 10;
        }
        if ($this->bonus) {
            $this->lastWin += $this->getWinAmount($this->getFreespinSymbol()->getTitle(), true);
        }
        $this->balance += $this->lastWin;
    }
}