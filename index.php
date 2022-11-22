<?php

include_once 'vendor/autoload.php';

use App\Slot;
use App\SymbolCollection;
use App\Symbol;

function displaySlot(array $slot): void
{
    for ($y = 0; $y < 3; $y++) {
        echo ' ';
        for ($x = 0; $x < 5; $x++) {
            echo $slot[$y][$x] !== null ? $slot[$y][$x]->getTitle() : '0';
            if ($x !== 4) {
                echo ' | ';
            }
        }
        echo PHP_EOL;
        if ($y !== 2) {
            echo '---*---*---*---*---' . PHP_EOL;
        }
    }
}

$payTable = [
    '9' => [0, 5, 25, 100],
    'J' => [0, 5, 25, 100],
    'Q' => [0, 5, 25, 100],
    'K' => [0, 5, 40, 150],
    'A' => [0, 5, 40, 150],
    '*' => [5, 35, 100, 350],
    '#' => [5, 35, 100, 350],
    'F' => [5, 40, 400, 2000],
    'M' => [10, 100, 1000, 5000],
    'B' => [10, 100, 1000, 5000]
];
$bonusWildPayTable = [2, 20, 200];
for ($y = 0; $y < 3; $y++) {
    for ($x = 0; $x < 5; $x++) {
        $slot[$y][$x] = null;
    }
}
$combinations = [
    [
        [0, 1], [1, 1], [2, 1], [3, 1], [4, 1]
    ],
    [
        [0, 0], [1, 0], [2, 0], [3, 0], [4, 0]
    ],
    [
        [0, 2], [1, 2], [2, 2], [3, 2], [4, 2]
    ],
    [
        [0, 0], [1, 1], [2, 2], [3, 1], [4, 0]
    ],
    [
        [0, 2], [1, 1], [2, 0], [3, 1], [4, 2]
    ],
    [
        [0, 1], [1, 0], [2, 0], [3, 0], [4, 1]
    ],
    [
        [0, 1], [1, 2], [2, 2], [3, 2], [4, 1]
    ],
    [
        [0, 2], [1, 1], [2, 1], [3, 1], [4, 0]
    ],
    [
        [0, 2], [1, 2], [2, 1], [3, 0], [4, 0]
    ],
    [
        [0, 0], [1, 0], [2, 1], [3, 2], [4, 2]
    ]
];
$variations = [
    "9" => 4,
    "J" => 4,
    "Q" => 4,
    "K" => 3,
    "A" => 3,
    "*" => 2,
    "#" => 2,
    "F" => 1,
    "M" => 1,
    "B" => 1
];
$bonusWild = 'B';
$symbols = new SymbolCollection();
foreach ($payTable as $symbol => $pay) {
    $symbols->add(new Symbol(
        $symbol,
        $variations[$symbol],
        $pay
    ));
}


$game = new Slot(
    $slot,
    $combinations,
    $bonusWildPayTable,
    1000,
    $symbols,
    $symbols->getSymbolByTitle($bonusWild)
);


$stake = $game->getMultiplier() * $game->getLines();
echo PHP_EOL;
displaySlot($slot);
echo "lines: {$game->getLines()}, multiplier: {$game->getMultiplier()} stake: $stake \n";
echo "Balance: {$game->getBalance()}\n";

//game cycle
while (true) {
    echo PHP_EOL;
    //player's choice
    $pattern = "/^d$|^t$|^e$|^l$|^m$/i";
    $input = readline('Spin: "Enter", PayTable: "t", change lines: "l", change multiplier: "m", exit: "e", deposit: "d"! your choice: ');
    if (!(preg_match($pattern, $input) || empty($input))) {
        echo "Incorrect choice!\n";
    }
    //exit
    $pattern = "/e/i";
    if (preg_match($pattern, $input)) {
        echo 'Hope to see you with your money again soon!' . PHP_EOL;
        exit;
    }
    //change lines in play
    $pattern = "/l/i";
    if (preg_match($pattern, $input)) {
        if ($game->getFreespins() === 0) {
            while (true) {
                $lines = (int)readline('Enter lines :');
                if ($lines > 0 && $lines <= 10) {
                    $game->setLines($lines);
                    break;
                }
            }
        } else {
            echo 'Sorry bonus play. Finish the free spins to change the lines' . PHP_EOL;
        }
        continue;
    }
    //change multiplier
    $pattern = "/m/i";
    if (preg_match($pattern, $input)) {
        if ($game->getFreespins() === 0) {
            while (true) {
                $multiplier = (int)readline('Enter multiplier: ');
                if ($multiplier > 0) {
                    $game->setMultiplier($multiplier);
                    break;
                }
            }
        } else {
            echo 'Sorry bonus play. Finish the free spins to change the multiplier' . PHP_EOL;
        }
        continue;
    }
    //deposit money
    $pattern = "/d/i";
    if (preg_match($pattern, $input)) {
        while (true) {
            $input = (int)readline('Enter balance: ');
            if ($input > 0) {
                $game->deposit($input);
                break;
            } else echo ('incorrect balance') . PHP_EOL;
        }
        continue;
    }
    //prints payout table and rules depending on the multiplier
    $pattern = "/t/i";
    if (preg_match($pattern, $input)) {
        echo PHP_EOL;
        /** @var Symbol $symbol */
        foreach ($game->getSymbols() as $symbol) {
            echo "{$symbol->getTitle()} - ";
            foreach ($symbol->getPrices() as $key => $pay) {
                if ($pay > 0) {
                    echo $key + 2 . ": " . $game->getMultiplier() * $pay . "  ";
                }
            }
            echo PHP_EOL;
        }
        echo "BonusWild symbol is {$game->getBonusWild()->getTitle()} ! 3+ triggers free spins with bonus symbol\n";
        echo "In bonus play bonus symbol expands in every column if encountered in than column once\n";
        echo "Bonus symbol can be any symbol expect {$game->getBonusWild()->getTitle()}\n";
        echo "BonusWild - ";
        for ($i = 0; $i < count($game->getBonusWildPayTable()); $i++) {

            echo $i + 3 . ": " . $game->getBonusWildPayTable()[$i] * $game->getMultiplier() . "  ";
        }
        echo "in any cell\n";
        continue;
    }
    //if user doesn't have enough money
    if ($game->getBalance() < $game->getStake() && $game->getFreespins() === 0) {
        echo 'Sorry insufficient balance. please deposit' . PHP_EOL;
        continue;
    }

    $game->spin();
    echo PHP_EOL;
    if ($game->isBonus())
    {
        echo 'Freespins left: ' . $game->getFreespins() . PHP_EOL;
        echo 'Expansion Symbol: ' . $game->getFreespinSymbol()->getTitle() . PHP_EOL;
    }

    displaySlot($game->getField());

    if ($game->getFreespins() > 0 && $game->isBonus())
    {
        if ($game->getWinAmount($game->getFreespinSymbol()->getTitle(), true) > 0)
        {
            echo PHP_EOL;
            displaySlot($game->getExpandBonusField());
        }

    }

    if ($game->getFreespins() > 0 && !$game->isBonus())
    {
        echo PHP_EOL . '10 Freespins awarded! Expansion Symbol: ' . $game->getFreespinSymbol()->getTitle() . PHP_EOL;
        echo PHP_EOL;
    }

    echo "lines: {$game->getLines()}, multiplier: {$game->getMultiplier()} stake: {$game->getStake()} \n";
    echo "You win: {$game->getLastWin()}\n";
    echo "Balance: {$game->getBalance()}\n";
}


