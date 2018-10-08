<?php

namespace SimpleSAML;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\RequestContext;

/**
 * Class that routes requests to responses.
 *
 * @package SimpleSAML
 */
class Router
{

    protected $arguments;

    /** @var RequestContext */
    protected $context;

    /** @var ModuleControllerResolver */
    protected $resolver;
    protected $dispatcher;
    protected $request;

    protected $stack;


    /**
     * Router constructor.
     *
     * @param string $module
     */
    public function __construct($module)
    {
        $this->arguments = new ArgumentResolver();
        $this->context = new RequestContext();
        $this->resolver = new ModuleControllerResolver($module);
        $this->dispatcher = new EventDispatcher();
    }


    /**
     * Process a given request.
     *
     * @param Request|null $request The request to process. Defaults to the current one.
     *
     * @return Response A response suitable for the given request.
     *
     * @throws \Exception If an error occurs.
     */
    public function process(Request $request = null)
    {
        $this->request = $request;
        if ($request === null) {
            $this->request = Request::createFromGlobals();
        }
        $stack = new RequestStack();
        $stack->push($this->request);
        $this->context->fromRequest($this->request);
        $kernel = new HttpKernel($this->dispatcher, $this->resolver, $stack, $this->resolver);
        return $kernel->handle($this->request);
    }


    /**
     * Send a given response to the browser.
     *
     * @param Response $response The response to send.
     */
    public function send(Response $response)
    {
        $response->prepare($this->request);
        $response->send();
    }
}
