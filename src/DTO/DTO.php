<?php

namespace WpMVC\DTO;

defined( 'ABSPATH' ) || exit;

use ReflectionClass;

/**
 * Abstract class DTO (Data Transfer Object)
 *
 * Provides a base implementation for DTOs that facilitates converting object properties
 * to an associative array and checking if properties are initialized.
 */
abstract class DTO {
    /**
     * Converts the object properties to an associative array.
     *
     * @param bool $with_id Optional. Whether to include the 'id' property in the output. Default false.
     * @return array The associative array of property names and their values.
     */
    public function to_array( bool $with_id = false ) {
        $reflection = new ReflectionClass( $this ); // Create a reflection class instance for the current object
        $values     = [];

        // Loop through each property of the object
        foreach ( $reflection->getProperties() as $property ) {
            $property_name = $property->getName();
            
            // Skip the 'id' property if $with_id is false
            if ( ! $with_id && 'id' === $property_name ) {
                continue;
            }

            // Access the property using reflection
            $prop = $reflection->getProperty( $property_name );
            $prop->setAccessible( true );

            // Check if the property is initialized
            if ( $prop->isInitialized( $this ) ) {
                // Use a method to get the property value if it's a boolean
                if ( $prop->getType() && 'bool' === $prop->getType()->getName() ) {
                    $value = $this->{"is_{$property_name}"}();
                } else {
                    // Otherwise, use the corresponding getter method
                    $value = $this->{"get_{$property_name}"}();
                }

                // Add the property name and value to the array
                $values[$property_name] = $value;
            }
        }

        return $values; // Return the associative array of properties and their values
    }

    /**
     * Checks if a specific property is initialized.
     *
     * @param string $property The property name to check.
     * @return bool True if the property is initialized, false otherwise.
     */
    public function is_initialized( string $property ): bool {
        $reflection = new ReflectionClass( $this ); // Create a reflection class instance for the current object

        // Check if the property exists in the class
        if ( ! $reflection->hasProperty( $property ) ) {
            return false;
        }
        
        // Access the property using reflection
        $prop = $reflection->getProperty( $property );
        $prop->setAccessible( true );
        
        // Return whether the property is initialized
        return $prop->isInitialized( $this );
    }
}
