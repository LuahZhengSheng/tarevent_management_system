<?php
// app/Decorators/BasePostDecorator.php

namespace App\Decorators;

use Illuminate\Http\Request;

class BasePostDecorator implements PostDecoratorInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var PostDecoratorInterface|null
     */
    protected $decorator;

    /**
     * Constructor - accepts either Request or another decorator
     *
     * @param Request|PostDecoratorInterface $requestOrDecorator
     */
    public function __construct($requestOrDecorator)
    {
        if ($requestOrDecorator instanceof Request) {
            $this->request = $requestOrDecorator;
            $this->decorator = null;
        } elseif ($requestOrDecorator instanceof PostDecoratorInterface) {
            $this->decorator = $requestOrDecorator;
            $this->request = $this->extractRequest($requestOrDecorator);
        } else {
            throw new \InvalidArgumentException(
                'Argument must be either Request or PostDecoratorInterface instance'
            );
        }
    }

    /**
     * Extract request from decorator chain
     *
     * @param PostDecoratorInterface $decorator
     * @return Request
     */
    protected function extractRequest(PostDecoratorInterface $decorator): Request
    {
        if (method_exists($decorator, 'getRequest')) {
            return $decorator->getRequest();
        }

        // Try to extract from protected property using reflection
        $reflection = new \ReflectionClass($decorator);
        
        if ($reflection->hasProperty('request')) {
            $property = $reflection->getProperty('request');
            $property->setAccessible(true);
            return $property->getValue($decorator);
        }

        throw new \RuntimeException('Cannot extract Request from decorator');
    }

    /**
     * Process the request data
     *
     * @return array
     */
    public function process(): array
    {
        // If there's a decorator in the chain, process it first
        if ($this->decorator) {
            return $this->decorator->process();
        }

        // Base processing - extract basic fields from request
        return [
            'title' => $this->request->input('title'),
            'content' => $this->request->input('content'),
            'category_id' => $this->request->input('category_id'),
            'visibility' => $this->request->input('visibility', 'public'),
            'status' => $this->request->input('status', 'published'),
            'club_id' => $this->request->input('club_id'),
        ];
    }

    /**
     * Get the request instance
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get the decorated instance
     *
     * @return PostDecoratorInterface|null
     */
    public function getDecorator(): ?PostDecoratorInterface
    {
        return $this->decorator;
    }
}
