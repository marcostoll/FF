<?php
/**
 * Definition of PreRender
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events\Templating;

use FF\DataStructures\Record;
use FF\Events\AbstractEvent;

/**
 * Class PreForward
 *
 * @package FF\Events\Templating
 */
class PreRender extends AbstractEvent
{
    /**
     * @var string
     */
    protected $template;

    /**
     * @var Record
     */
    protected $data;

    /**
     * @param string $template
     * @param Record $data
     */
    public function __construct(string $template, Record $data)
    {
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return Record
     */
    public function getData(): Record
    {
        return $this->data;
    }
}