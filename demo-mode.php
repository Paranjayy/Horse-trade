<?php
// Demo Mode Configuration
// Set this to true to run without database (for Vercel demo)
define('DEMO_MODE', true);

// Mock data for demo mode
function getDemoHorses() {
    return [
        [
            'id' => 1,
            'name' => 'Thunder',
            'breed' => 'Arabian',
            'age' => 8,
            'gender' => 'gelding',
            'price' => 15000,
            'location' => 'Kentucky, USA',
            'description' => 'Beautiful Arabian gelding with excellent bloodlines.',
            'image' => 'horse_photo.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Lightning Bolt',
            'breed' => 'Thoroughbred', 
            'age' => 5,
            'gender' => 'stallion',
            'price' => 25000,
            'location' => 'Kentucky, USA',
            'description' => 'Young stallion with racing potential.',
            'image' => 'horse_photo.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Sweet Dreams',
            'breed' => 'Quarter Horse',
            'age' => 12,
            'gender' => 'mare',
            'price' => 8000,
            'location' => 'Texas, USA', 
            'description' => 'Gentle mare perfect for beginners.',
            'image' => 'horse_photo.jpg'
        ]
    ];
}

function getDemoCategories() {
    return [
        ['id' => 1, 'name' => 'Arabian'],
        ['id' => 2, 'name' => 'Thoroughbred'],
        ['id' => 3, 'name' => 'Quarter Horse'],
        ['id' => 4, 'name' => 'Warmblood']
    ];
}

// Check if we should use demo mode
function isDemoMode() {
    return DEMO_MODE || !isset($GLOBALS['conn']) || !$GLOBALS['conn'];
}
?> 