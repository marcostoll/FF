<?php
/**
 * Definition of TwigRenderer
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */

declare(strict_types=1);

namespace FF\Services\Templating;

use FF\DataStructures\Record;
use FF\Services\AbstractService;
use FF\Services\Templating\Exceptions\RenderingException;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class TwigRenderer
 *
 * Options:
 *
 *  - twig-options  : array (default: [])       - options to configure twig environment
 *  - template-dir  : string                    - path to templates folder
 *  - fire-events   : bool (default: false)     - whether to fire rendering events
 *
 * @method void addRuntimeLoader(RuntimeLoaderInterface $loader)
 * @method void addExtension(ExtensionInterface $extension)
 * @method void addTokenParser(TokenParserInterface $parser)
 * @method void addNodeVisitor(NodeVisitorInterface $visitor)
 * @method void addFilter(TwigFilter $filter)
 * @method void addTest(TwigTest $test)
 * @method void addFunction(TwigFunction $function)
 * @method void addGlobal(string $name, mixed $value)
 *
 * @package FF\Services\Templating
 *
 * @link https://twig.symfony.com/doc/2.x/api.html#environment-options Twig Environment options
 * @link https://twig.symfony.com/doc/2.x/advanced.html Extending Twig
 */
class TwigRenderer extends AbstractService implements TemplateRendererInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var bool
     */
    protected $fireEvents;

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @param Environment $twig
     * @return $this
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasFireEvents(): bool
    {
        return $this->fireEvents;
    }

    /**
     * @param bool $fireEvents
     * @return $this
     */
    public function setFireEvents(bool $fireEvents)
    {
        $this->fireEvents = $fireEvents;
        return $this;
    }

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
    public function render(string $template, array $data): string
    {
        if ($this->fireEvents) {
            // wrap data in object to support data manipulation via event listener
            $record = new Record($data);
            $this->fire('Templating\PreRender', $template, $record);
            $data = $record->getDataAsArray();
        }

        try {
            $contents = $this->twig->render($template, $data);
        } catch (Error $e) {
            throw new RenderingException($e->getMessage(), $e->getCode(), $e);
        }

        if ($this->fireEvents) {
            // wrap data in object to support data manipulation via event listener
            $doc = new RenderedDocument($contents);
            $this->fire('Templating\PostRender', $doc);
            $contents = $doc->getContents();
        }

        return $contents;
    }

    /**
     * Magic proxy for the public api of Twig\Environment
     *
     * Routes the method call to the twig environment instance encapsulated
     * within this service.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \BadMethodCallException Method is not defined or accessible on Twig\Environment
     */
    public function __call(string $name, array $arguments = [])
    {
        $callable = [$this->twig, $name];
        if (!is_callable($callable)) {
            // trigger fatal error: unsupported method call
            // mimic standard php error message
            // Fatal error: Call to undefined method {class}::{method}() in {file} on line {line}
            $backTrace = debug_backtrace();
            $errorMsg = 'Call to undefined method ' . __CLASS__ . '::' . $name . '() '
                . 'in ' . $backTrace[0]['file'] . ' on line ' . $backTrace[0]['line'];
            trigger_error($errorMsg, E_USER_ERROR);
        }

        return call_user_func_array($callable, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(array $options)
    {
        parent::initialize($options);

        $this->fireEvents = $this->getOption('fire-events', false);

        $loader = new FilesystemLoader($this->getOption('template-dir'));
        $this->twig = new Environment($loader, $this->getOption('twig-options', []));
    }

    /**
     * {@inheritDoc}
     */
    protected function validateOptions(array $options, array &$errors): bool
    {
        if (!isset($options['template-dir']) || empty($options['template-dir'])) {
            $errors[] = 'missing or empty mandatory option [template-dir]';
        }

        return empty($errors);
    }
}
