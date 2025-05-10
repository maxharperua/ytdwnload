<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $content = Cache::remember('sitemap', 3600, function () {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');
            
            $url = $xml->addChild('url');
            $url->addChild('loc', 'https://ytload.ru/');
            $url->addChild('lastmod', now()->format('Y-m-d'));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '1.0');
            
            return $xml->asXML();
        });
        
        return response($content, 200)
            ->header('Content-Type', 'text/xml');
    }
} 