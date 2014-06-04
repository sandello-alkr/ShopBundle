<?php

namespace alkr\ShopBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ShopExtension extends Extension implements PrependExtensionInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$container->setParameter('shop', $config);

		$filters = $container->getParameter('imagine.filters');
		$filters['cart_thumb'] = array(
									'type'  => 'thumbnail',
									'options'  => array(
										'size' => array(200,200),
										'mode' => 'inset'
										)
									);
		$container->setParameter('imagine.filters',$filters);

		// $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		// $loader->load('config.yml');
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepend(ContainerBuilder $container)
	{
		$bundles = $container->getParameter('kernel.bundles');

		if (true === isset($bundles['AvalancheImagineBundle'])) {
			foreach (array_keys($container->getExtensions()) as $name) {
				switch ($name) {
					case 'dsafd':
					$container->prependExtensionConfig(
						$name,
						array(
							'filters' => array(
								'cart_thumb' => array(
									'type'  => 'thumbnail',
									'options'  => array(
										'size' => array(200,200),
										'mode' => 'inset'
										)
									)
								)
							)
						);
					break;
				}
			}
		}
	}
}
