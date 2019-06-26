<?php
/**
 * Definition of PostRender
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events\Templating;

use FF\Events\AbstractEvent;
use FF\Services\Templating\RenderedDocument;

/**
 * Class PostRender
 *
 * @package FF\Events\Templating
 */
class PostRender extends AbstractEvent
{
    /**
     * @var RenderedDocument
     */
    protected $doc;

    /**
     * @param RenderedDocument $doc
     */
    public function __construct(RenderedDocument $doc)
    {
        $this->doc = $doc;
    }

    /**
     * @return RenderedDocument
     */
    public function getDoc(): RenderedDocument
    {
        return $this->doc;
    }
}
