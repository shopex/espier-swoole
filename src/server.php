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
        if (isset($this->lockFile)) {
            file_put_contents($this->lockFile, $server->master_pid);
        }
    }

    public function onRequest(swoole_http_request $swooleRequest, swoole_http_response $swooleResponse)
    {
        clearstatcache();
        
        $symfonyRequest = $this->dealWithRequest($swooleRequest);

        $response = $this->app->handle($symfonyRequest);

        if ($response instanceof symfonyResponse) {
            $symfonyResponse = $response->prepare($symfonyRequest);
            $this->dealWithResponse($symfonyRequest, $symfonyResponse, $swooleResponse);
        } else {
            $swooleResponse->end(strval($response));
        }
    }

    /**
     * convert swoole request to symfony request
     *
     * @param swoole_http_request $request
     *
     * @return Request
     * */
    protected function dealWithRequest(swoole_http_request $swooleRequest)
    {
        $get     = isset($swooleRequest->get) ? $swooleRequest->get : [];
        $post    = isset($swooleRequest->post) ? $swooleRequest->post : [];
        $server  = isset($swooleRequest->server) ? $swooleRequest->server : [];
        $header  = isset($swooleRequest->header) ? $swooleRequest->header : [];
        $files   = isset($swooleRequest->files) ? $swooleRequest->files : [];
        $cookie = isset($swooleRequest->cookie) ? $swooleRequest->cookie : [];

        foreach($header as $key => $value) {
            $server['http_'.$key] = $value;
        }

        foreach($server as $key => $value) {
            $newServer[strtoupper($key)] = $value;
        }
        

        $content = $swooleRequest->rawContent() ?: null;

        $symfonyRequest = new symfonyRequest($get, $post, []/* attributes */, $cookie, $files, $newServer, $content);

        return $symfonyRequest;
    }

    protected function dealWithResponse(symfonyRequest $symfonyRequest, symfonyResponse $symfonyResponse, swoole_http_response $swooleResponse)
    {
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
        $swooleResponse->end($symfonyResponse->getContent());
    }
}