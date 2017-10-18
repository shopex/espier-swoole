<?php

namespace Espier\Swoole;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Laravel\Lumen\Application;
use swoole_http_server;
use swoole_http_request;
use swoole_http_response;

/**
 * Espier swoole server
 *
 * 目前目的只是针对API, 因此cookie的相关处理全部忽略掉
 * @author Bryant Yan <bryant.yan@gmail.com>
 */
class Server
{
    protected $lockFile;

    protected $swooleServer;

    protected $app;

    public function __construct(Application $app, $lockFile = null)
    {
        $this->swooleServer = new swoole_http_server($app['config']['server.host'],
                                                     $app['config']['server.port']);

        $this->swooleServer->set($app['config']['server.options']);
        $this->lockFile = $lockFile;
        $this->app = $app;
    }

    public function run()
    {
        $this->swooleServer->on('request', [$this, 'onRequest']);
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->start();
    }

    public function onStart(swoole_http_server $server)
    {
        /**
         * if ($files = $this->app['config']['server.worker_start_include']) {
         *     $filesystem = $this->app['files'];
         *     foreach($files as $file) {
         *         $fileName = $this->app->basePath('bootstrap').'/'.$file;
         *         if ($filesystem->isFile($fileName)) {
         *             $filesystem->requireOnce($fileName);
         *         }
         *     }
         * }
         */
        //require_once $this->app->basePath().'/bootstrap/route.php';


        if (isset($this->lockFile)) {
            file_put_contents($this->lockFile, $server->master_pid);
        }
    }

    public function onRequest(swoole_http_request $swooleRequest, swoole_http_response $swooleResponse)
    {
        $symfonyRequest = $this->handleRequest($swooleRequest);
        $symfonyResponse = $this->app->handle($symfonyRequest);

        $this->clean($symfonyRequest);

        return $this->handleResponse($symfonyRequest, $swooleResponse, $symfonyResponse);
    }

    /**
     * convert swoole request to symfony request
     *
     * @param swoole_http_request $request
     *
     * @return Request
     * */
    protected function handleRequest(swoole_http_request $swooleRequest)
    {
        clearstatcache();

        $get     = isset($swooleRequest->get) ? $swooleRequest->get : [];
        $post    = isset($swooleRequest->post) ? $swooleRequest->post : [];
        $attributes = [];
        $files   = isset($swooleRequest->files) ? $swooleRequest->files : [];
        $cookie = isset($swooleRequest->cookie) ? $swooleRequest->cookie : [];
        $server = isset($swooleRequest->server) ? array_change_key_case($swooleRequest->server, CASE_UPPER) : [];

        if (isset($swooleRequest->header)) {
            foreach ($swooleRequest->header as $key => $value) {
                $newKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                $server[$newKey] = $value;
            }
        }

        $content = $swooleRequest->rawContent() ?: null;

        $symfonyRequest = new symfonyRequest($get, $post, $attributes, $cookie, $files, $server, $content);

        return $symfonyRequest;
    }

    protected function handleResponse(symfonyRequest $symfonyRequest, swoole_http_response $swooleResponse, symfonyResponse $symfonyResponse)
    {
        if (!$symfonyResponse instanceof symfonyResponse) {
            return $swooleResponse->end(strval($symfonyResponse));
        }

        $symfonyResponse = $symfonyResponse->prepare($symfonyRequest);

        // status
        $swooleResponse->status($symfonyResponse->getStatusCode());

        // header
        foreach($symfonyResponse->headers->allPreserveCase() as $name => $values) {
            foreach($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        // zip
        $swooleResponse->gzip();

        // content
        return $swooleResponse->end($symfonyResponse->getContent());
    }

    protected function clean(symfonyRequest $request)
    {
        if ($this->app->bound('registry')) {
            foreach (config('doctrine.managers') as $mangerName => $v) {
                app('registry')->getManager($mangerName)->getConfiguration()->getResultCacheImpl()->flushAll();
                app('registry')->getManager($mangerName)->getConfiguration()->getQueryCacheImpl()->flushAll();
                app('registry')->getManager($mangerName)->clear();
                $this->app['api.auth']->setUser(null);
            }
        }
    }
}
