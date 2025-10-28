<?php

namespace App\Http\Controllers;

class CheckAnimationController extends Controller
{
    public function svg()
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 120 120" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Success" style="display:block">
  <style>
    .circle { fill: none; stroke: #22c55e; stroke-width: 6; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 314; stroke-dashoffset: 314; animation: drawCircle 0.6s ease-out forwards; transform-origin: 60px 60px; }
    .check  { fill: none; stroke: #ffffff; stroke-width: 6; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawCheck 0.45s ease-out 0.5s forwards; }
    .bg    { fill: #22c55e; opacity: 0; animation: fadeInBg 0.2s ease-out 0.5s forwards; }
    @keyframes drawCircle { 
      0% { stroke-dashoffset: 314; }
      100% { stroke-dashoffset: 0; } 
    }
    @keyframes drawCheck { 
      0% { stroke-dashoffset: 50; }
      100% { stroke-dashoffset: 0; } 
    }
    @keyframes fadeInBg { 
      0% { opacity: 0; }
      100% { opacity: 1; } 
    }
  </style>

  <circle class="bg" cx="60" cy="60" r="52"/>
  <circle class="circle" cx="60" cy="60" r="50"/>
  <path class="check" d="M40 62 L54 76 L82 44" />
</svg>
SVG;

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
