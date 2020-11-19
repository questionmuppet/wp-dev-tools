<?php
/**
 * Url
 * 
 * Url to external resource
 */

namespace Wp_Dev_Tools\Data;

final class Url
{
    /**
     * Url value
     */
    private $url;

    /**
     * Constructor
     */
    public function __construct(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
        {
            throw new \InvalidArgumentException(sprintf(
                "Invalid url provided while trying to instantiate a %s. '%s' does not match a known url pattern.",
                get_called_class(),
                $url
            ));
        };
        $this->url = $url;
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->url;
    }
}