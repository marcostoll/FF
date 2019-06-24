<?php
/**
 * Definition of RenderedDocument
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Templating;

/**
 * Class RenderedDocument
 *
 * @package FF\Services\Templating
 */
class RenderedDocument
{
    /**
     * @var string
     */
    private $contents;

    /**
     * @param string $contents
     */
    public function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     * @return $this
     */
    public function setContents(string $contents)
    {
        $this->contents = $contents;
        return $this;
    }
}
