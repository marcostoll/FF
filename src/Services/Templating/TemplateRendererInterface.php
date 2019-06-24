<?php
/**
 * Definition of TemplateRendererInterface
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Templating;

use FF\Services\Templating\Exceptions\RenderingException;

/**
 * Interface TemplateRendererInterface
 *
 * @package FF\Services\Templating
 */
interface TemplateRendererInterface
{
    /**
     * Renders a template using the given data
     *
     * @param string $template
     * @param array $data
     * @return string
     * @throws RenderingException
     * @fires Templating\PreRender
     * @fires Templating\PostRender
     */
    public function render(string $template, array $data): string;
}
