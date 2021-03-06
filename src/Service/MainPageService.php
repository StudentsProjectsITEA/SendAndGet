<?php

/*
 * This file is part of the "Send And Get" project.
 * (c) Sergey Rybak <srybak007@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\DTO\BreadcrumbsDTO;
use App\DTO\MainPageDTO;
use App\Exceptions\EntityNotFoundException;
use App\Repository\PageRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class MainPageService implements MainPageServiceInterface
{
    private $pageRepo;

    public function __construct(PageRepositoryInterface $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    public function getAboutUs(string $lang = 'en'): MainPageDTO
    {
        return $this->getBySlug('about_us', $lang);
    }

    public function getContuctAs(string $lang = 'en'): MainPageDTO
    {
        return $this->getBySlug('contact_us', $lang);
    }

    public function getStatistic(string $lang = 'en'): MainPageDTO
    {
        return $this->getBySlug('statistic', $lang);
    }

    public function getTOS(string $lang = 'en'): MainPageDTO
    {
        return $this->getBySlug('tos', $lang);
    }

    public function getAPI(string $lang = 'en'): MainPageDTO
    {
        return $this->getBySlug('api', $lang);
    }

    public function getFAQ(string $lang = 'en'): MainPageDTO
    {
        return $this->getBySlug('faq', $lang);
    }

    private function getBySlug(string $slug, string $lang = 'en'): MainPageDTO
    {
        $page = $this->pageRepo->getBySlug($slug, $lang);

        if (null === $page) {
            throw new EntityNotFoundException("Page $slug ($lang) not found");
        }

        $page->setCurrentLocale($lang);

        return new MainPageDTO($page, [
            new BreadcrumbsDTO('Main', 'index'),
            new BreadcrumbsDTO($page->getTitle(), $slug),
        ]);
    }

    public function getCurrentLocale(): string
    {
        $locale = $this->getSession()->get('locale');

        return $locale ? $locale : 'en';
    }

    public function setCurrentLocale(string $locale): void
    {
        $this->getSession()->set('locale', $locale);
    }

    private function getSession(): Session
    {
        $session = new Session();

        if (!$session->getId()) {
            $session->start();
        }

        return $session;
    }
}
