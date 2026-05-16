<?php

return [
    'name' => 'Interlude Coffee & Tea',
    'tagline' => 'high quality coffee, matcha, tea, and house-baked pastries',
    'default_description' => 'Aplikasi reservasi cafe Interlude Coffee & Tea.',

    'nav' => [
        ['key' => 'landing', 'label' => 'Landing', 'route' => 'landing'],
        ['key' => 'menu', 'label' => 'Menu', 'route' => 'menu'],
        ['key' => 'cart', 'label' => 'Cart', 'route' => 'cart'],
        ['key' => 'about', 'label' => 'About', 'route' => 'about'],
    ],

    'menu_items' => [
        [
            'id' => 'signature-latte',
            'name' => 'Signature Latte',
            'category' => 'drink',
            'price' => 38000,
            'description' => 'Espresso lembut dengan susu creamy dan aroma caramel tipis.',
            'badge' => 'Best Seller',
        ],
        [
            'id' => 'matcha-cloud',
            'name' => 'Matcha Cloud',
            'category' => 'drink',
            'price' => 42000,
            'description' => 'Matcha premium, susu segar, dan foam vanilla ringan.',
            'badge' => 'Favorite',
        ],
        [
            'id' => 'black-tea-lemon',
            'name' => 'Black Tea Lemon',
            'category' => 'drink',
            'price' => 30000,
            'description' => 'Teh hitam dingin dengan lemon segar dan aftertaste clean.',
            'badge' => 'Fresh',
        ],
        [
            'id' => 'espresso-tonic',
            'name' => 'Espresso Tonic',
            'category' => 'drink',
            'price' => 40000,
            'description' => 'Espresso single origin dipadukan tonic sparkling.',
            'badge' => 'Sparkling',
        ],
        [
            'id' => 'butter-croissant',
            'name' => 'Butter Croissant',
            'category' => 'pastry',
            'price' => 32000,
            'description' => 'Croissant flaky dengan aroma butter yang rich.',
            'badge' => 'House Baked',
        ],
        [
            'id' => 'almond-pain',
            'name' => 'Almond Pain',
            'category' => 'pastry',
            'price' => 36000,
            'description' => 'Pastry almond dengan filling lembut dan toasted almond.',
            'badge' => 'Sweet',
        ],
        [
            'id' => 'cinnamon-roll',
            'name' => 'Cinnamon Roll',
            'category' => 'pastry',
            'price' => 34000,
            'description' => 'Roll kayu manis hangat dengan glaze tipis.',
            'badge' => 'Warm',
        ],
        [
            'id' => 'banana-bread',
            'name' => 'Banana Bread',
            'category' => 'pastry',
            'price' => 28000,
            'description' => 'Banana bread moist dengan hint dark chocolate.',
            'badge' => 'Classic',
        ],
    ],
];
