<?php

namespace App\Twig;

use App\Entity\SiteSetting;
use App\Repository\SiteSettingRepository;
use App\Service\MarkdownRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SiteExtension extends AbstractExtension
{
    private ?SiteSetting $cache = null;

    public function __construct(private readonly SiteSettingRepository $settings, private readonly MarkdownRenderer $markdown) {}

    public function getFunctions(): array
    {
        return [new TwigFunction('site', $this->site(...))];
    }

    public function getFilters(): array
    {
        return [new TwigFilter('markdown', $this->markdown->render(...), ['is_safe' => ['html']])];
    }

    public function site(): SiteSetting
    {
        if ($this->cache === null) {
            try {
                $this->cache = $this->settings->get();
            } catch (\Throwable) {
                $this->cache = new SiteSetting(); // base de données pas encore créée : valeurs par défaut
            }
        }

        return $this->cache;
    }
}
