<?php

namespace DDTrace\Tests\Integrations\Symfony\V2_8;

use DDTrace\Tests\Common\SpanAssertion;
use DDTrace\Tests\Common\WebFrameworkTestCase;
use DDTrace\Tests\Frameworks\Util\Request\GetSpec;

final class RouteNameTest extends WebFrameworkTestCase
{
    protected static function getAppIndexScript()
    {
        return __DIR__ . '/../../../Frameworks/Symfony/Version_2_8/web/app_dev.php';
    }

    /**
     * @throws \Exception
     */
    public function testResource2UriMapping()
    {
        $traces = $this->tracesFromWebRequest(function () {
            $spec  = GetSpec::create('Resource name properly set to route', '/');
            $this->call($spec);
        });

        $this->assertExpectedSpans($this, $traces, [
            SpanAssertion::build(
                'web.request',
                'web.request',
                'web',
                'AppBundle\Controller\DefaultController testingRouteNameAction'
            )->withExactTags([
                'http.method' => 'GET',
                'http.url' => '/',
                'http.status_code' => '200',
            ]),
        ]);
    }
}
