<?php

namespace WpMVC;

defined( 'ABSPATH' ) || exit;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Container wrapper around Symfony's ContainerBuilder
 * Provides auto-registration and singleton behavior on get()
 */
class Container
{
    protected ContainerBuilder $container;

    public function __construct() {
        $this->container = new ContainerBuilder();
    }

    /**
     * Get a service from the container.
     * Auto-registers the service as a singleton if not already registered.
     *
     * @param string $id
     * @return mixed
     */
    public function get( string $id, array $params = [] ) {
        // If service doesn't exist, auto-register it
        if ( ! $this->container->has( $id ) ) {
            // Register as singleton (default Symfony behavior)
            $definition = $this->container->register( $id, $id )
                ->setPublic( true )
                ->setAutowired( true );

            if ( ! empty( $params ) ) {
                $definition->setArguments( $params );
            }
        }

        return $this->container->get( $id );
    }

    /**
     * Set a service instance directly (bypass autowiring)
     *
     * @param string $id
     * @param mixed $service
     * @return void
     */
    public function set( string $id, $service ): void {
        $this->container->set( $id, $service );
    }

    /**
     * Check if container has a service
     *
     * @param string $id
     * @return bool
     */
    public function has( string $id ): bool {
        return $this->container->has( $id );
    }

    /**
     * Alias for get() - for compatibility
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make( string $abstract, array $parameters = [] ) {
        return $this->get( $abstract, $parameters );
    }

    /**
     * Get the underlying Symfony container
     *
     * @return ContainerBuilder
     */
    public function get_symfony_container(): ContainerBuilder {
        return $this->container;
    }
}
