<?php
declare(strict_types=1);

namespace LRPHPT\MenuTree\Factory;

use Psr\Container\ContainerInterface;
use LRPHPT\Navigation\Service\LrphptNavigationFactory;
use Laminas\Navigation\Exception\InvalidArgumentException;
use LRPHPT\MenuTree\Entity\Menu;

class MenuFactory extends LrphptNavigationFactory{

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getPages(ContainerInterface $container)
    {
        if (null === $this->pages) {
            $menuRepository = $container->get('doctrine.entitymanager.orm_default')->getRepository(Menu::class);
            $menuPages = $menuRepository->getMenuPages($this->getName());
            $pages       = $this->getPagesFromConfig($menuPages);
            $this->pages = $this->preparePages($container, $pages);
        }

        return $this->pages;
    }
}