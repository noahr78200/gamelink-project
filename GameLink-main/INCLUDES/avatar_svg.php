<?php
function generateAvatarSVG($avatar_config, $size = 100) {
    if (!$avatar_config) {
        $config = [
            'skinColor' => '#F4C2A0',
            'faceType' => 'face1',
            'hairStyle' => 'style1',
            'hairColor' => '#4A3728'
        ];
    } else {
        $config = json_decode($avatar_config, true);
    }
    
    $faces = [
        'face1' => '<circle cx="70" cy="90" r="8" fill="#000" />
                    <circle cx="130" cy="90" r="8" fill="#000" />
                    <circle cx="72" cy="88" r="3" fill="#FFF" />
                    <circle cx="132" cy="88" r="3" fill="#FFF" />
                    <path d="M 100 95 Q 95 110 100 115 Q 105 110 100 95" fill="none" stroke="#000" stroke-width="2" />
                    <path d="M 80 130 Q 100 145 120 130" fill="none" stroke="#000" stroke-width="3" stroke-linecap="round" />',
        
        'face2' => '<ellipse cx="70" cy="90" rx="10" ry="12" fill="#000" />
                    <ellipse cx="130" cy="90" rx="10" ry="12" fill="#000" />
                    <circle cx="72" cy="87" r="4" fill="#FFF" />
                    <circle cx="132" cy="87" r="4" fill="#FFF" />
                    <circle cx="100" cy="110" r="4" fill="#000" opacity="0.7" />
                    <path d="M 75 130 Q 100 150 125 130" fill="none" stroke="#000" stroke-width="3" stroke-linecap="round" />',
        
        'face3' => '<ellipse cx="70" cy="88" rx="14" ry="6" fill="#000" />
                    <ellipse cx="130" cy="88" rx="14" ry="6" fill="#000" />
                    <circle cx="77" cy="87" r="4" fill="#FFF" />
                    <circle cx="137" cy="87" r="4" fill="#FFF" />
                    <line x1="100" y1="100" x2="100" y2="115" stroke="#000" stroke-width="2" />
                    <path d="M 85 135 Q 100 140 115 135" fill="none" stroke="#000" stroke-width="2.5" stroke-linecap="round" />'
    ];
    
    $hairs = [
        'style1' => function($color) {
            return '<ellipse cx="100" cy="60" rx="85" ry="55" fill="' . $color . '" />
                    <rect x="15" y="50" width="170" height="40" fill="' . $color . '" />';
        },
        'style2' => function($color) {
            return '<ellipse cx="100" cy="55" rx="90" ry="60" fill="' . $color . '" />
                    <path d="M 20 80 Q 15 120 30 140 L 40 90 Z" fill="' . $color . '" />
                    <path d="M 180 80 Q 185 120 170 140 L 160 90 Z" fill="' . $color . '" />
                    <rect x="10" y="50" width="180" height="50" fill="' . $color . '" />';
        },
        'style3' => function($color) {
            return '<circle cx="100" cy="65" r="70" fill="' . $color . '" />
                    <circle cx="50" cy="70" r="45" fill="' . $color . '" />
                    <circle cx="150" cy="70" r="45" fill="' . $color . '" />
                    <circle cx="70" cy="40" r="35" fill="' . $color . '" />
                    <circle cx="130" cy="40" r="35" fill="' . $color . '" />';
        }
    ];
    
    $faceHTML = $faces[$config['faceType']];
    $hairHTML = $hairs[$config['hairStyle']]($config['hairColor']);
    
    return '<svg viewBox="0 0 200 200" style="width: ' . $size . 'px; height: ' . $size . 'px; border-radius: 50%;">
                <circle cx="100" cy="100" r="95" fill="#f0f0f0" />
                <g>' . $hairHTML . '</g>
                <circle cx="100" cy="100" r="70" fill="' . $config['skinColor'] . '" />
                <g>' . $faceHTML . '</g>
            </svg>';
}
?>