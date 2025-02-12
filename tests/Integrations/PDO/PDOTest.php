<?php

namespace DDTrace\Tests\Integrations\PDO;

use DDTrace\Integrations\IntegrationsLoader;
use DDTrace\Tests\Common\IntegrationTestCase;
use DDTrace\Tests\Common\SpanAssertion;

define('MYSQL_DATABASE', 'test');
define('MYSQL_USER', 'test');
define('MYSQL_PASSWORD', 'test');
define('MYSQL_HOST', 'mysql_integration');

final class PDOTest extends IntegrationTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        IntegrationsLoader::load();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function tearDown()
    {
        $this->clearDatabase();
        parent::tearDown();
    }

    public function testPDOContructOk()
    {
        $traces = $this->isolateTracer(function () {
                $this->pdoInstance();
        });
        $this->assertSpans($traces, [
            SpanAssertion::build('PDO.__construct', 'PDO', 'sql', 'PDO.__construct')
                ->withExactTags([]),
        ]);
    }

    public function testPDOContructError()
    {
        $traces = $this->isolateTracer(function () {
            try {
                new \PDO($this->mysqlDns(), 'wrong_user', 'wrong_password');
            } catch (\PDOException $ex) {
            }
        });
        $this->assertSpans($traces, [
            SpanAssertion::build('PDO.__construct', 'PDO', 'sql', 'PDO.__construct')
                ->setError('PDOException', 'Sql error: SQLSTATE[HY000] [1045]'),
        ]);
    }

    public function testPDOExecOk()
    {
        $query = "INSERT INTO tests (id, name) VALUES (1000, 'Sam')";
        $traces = $this->isolateTracer(function () use ($query) {
            $pdo = $this->pdoInstance();
            $pdo->beginTransaction();
            $pdo->exec($query);
            $pdo->commit();
            $pdo = null;
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.exec', 'PDO', 'sql', $query)
                ->setTraceAnalyticsCandidate()
                ->withExactTags(array_merge($this->baseTags(), [
                    'db.rowcount' => '1',
                ])),
            SpanAssertion::exists('PDO.commit'),
        ]);
    }

    public function testPDOExecError()
    {
        $query = "WRONG QUERY)";
        $traces = $this->isolateTracer(function () use ($query) {
            try {
                $pdo = $this->pdoInstance();
                $pdo->beginTransaction();
                $pdo->exec($query);
                $pdo->commit();
                $pdo = null;
            } catch (\PDOException $ex) {
            }
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.exec', 'PDO', 'sql', $query)
                ->setTraceAnalyticsCandidate()
                ->setError('PDO error', 'SQL error: 42000. Driver error: 1064')
                ->withExactTags(array_merge($this->baseTags(), [
                    'db.rowcount' => '',
                ])),
            SpanAssertion::exists('PDO.commit'),
        ]);
    }

    public function testPDOExecException()
    {
        $query = "WRONG QUERY)";
        $traces = $this->isolateTracer(function () use ($query) {
            try {
                $pdo = $this->pdoInstance();
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->beginTransaction();
                $pdo->exec($query);
                $pdo->commit();
                $pdo = null;
                $this->fail('Should throw and exception');
            } catch (\PDOException $ex) {
            }
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.exec', 'PDO', 'sql', $query)
                ->setTraceAnalyticsCandidate()
                ->setError('PDOException', 'Sql error')
                ->withExactTags($this->baseTags()),
        ]);
    }

    public function testPDOQuery()
    {
        $query = "SELECT * FROM tests WHERE id=1";
        $traces = $this->isolateTracer(function () use ($query) {
            $pdo = $this->pdoInstance();
            $pdo->query($query);
            $pdo = null;
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.query', 'PDO', 'sql', $query)
                ->setTraceAnalyticsCandidate()
                ->withExactTags(array_merge($this->baseTags(), [
                    'db.rowcount' => '1',
                ])),
        ]);
    }

    public function testPDOQueryError()
    {
        $query = "WRONG QUERY";
        $traces = $this->isolateTracer(function () use ($query) {
            try {
                $pdo = $this->pdoInstance();
                $pdo->query($query);
                $pdo = null;
            } catch (\PDOException $ex) {
            }
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.query', 'PDO', 'sql', $query)
                ->setTraceAnalyticsCandidate()
                ->setError('PDO error', 'SQL error: 42000. Driver error: 1064')
                ->withExactTags(array_merge($this->baseTags(), [
                    'db.rowcount' => '',
                ])),
        ]);
    }

    public function testPDOQueryException()
    {
        $query = "WRONG QUERY";
        $traces = $this->isolateTracer(function () use ($query) {
            try {
                $pdo = $this->pdoInstance();
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->query($query);
                $pdo = null;
            } catch (\PDOException $ex) {
            }
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.query', 'PDO', 'sql', $query)
                ->setTraceAnalyticsCandidate()
                ->setError('PDOException', 'Sql error')
                ->withExactTags($this->baseTags()),
        ]);
    }

    public function testPDOCommit()
    {
        $query = "INSERT INTO tests (id, name) VALUES (1000, 'Sam')";
        $traces = $this->isolateTracer(function () use ($query) {
            $pdo = $this->pdoInstance();
            $pdo->beginTransaction();
            $pdo->exec($query);
            $pdo->commit();
            $pdo = null;
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::exists('PDO.exec'),
            SpanAssertion::build('PDO.commit', 'PDO', 'sql', 'PDO.commit')
                ->withExactTags(array_merge($this->baseTags(), [])),
        ]);
    }

    public function testPDOStatementOk()
    {
        $query = "SELECT * FROM tests WHERE id = ?";
        $traces = $this->isolateTracer(function () use ($query) {
            $pdo = $this->pdoInstance();
            $stmt = $pdo->prepare($query);
            $stmt->execute([1]);
            $results = $stmt->fetchAll();
            $this->assertEquals('Tom', $results[0]['name']);
            $stmt->closeCursor();
            $stmt = null;
            $pdo = null;
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build(
                'PDO.prepare',
                'PDO',
                'sql',
                "SELECT * FROM tests WHERE id = ?"
            )->withExactTags(array_merge($this->baseTags(), [])),
            SpanAssertion::build(
                'PDOStatement.execute',
                'PDO',
                'sql',
                "SELECT * FROM tests WHERE id = ?"
            )
                ->setTraceAnalyticsCandidate()
                ->withExactTags(array_merge($this->baseTags(), [
                'db.rowcount' => 1,
                ])),
        ]);
    }

    public function testPDOStatementIsCorrectlyClosedOnUnset()
    {
        $query = "SELECT * FROM tests WHERE id > ?";
        $pdo = $this->ensureActiveQueriesErrorCanHappen();
        $this->isolateTracer(function () use ($query, $pdo) {
            $stmt = $pdo->prepare($query);
            $stmt->execute([10]);
            $stmt->fetch();
            unset($stmt);

            $stmt2 = $pdo->prepare($query);
            $stmt2->execute([10]);
            $stmt2->fetch();
        });
    }

    public function testPDOStatementCausesActiveQueriesError()
    {
        $query = "SELECT * FROM tests WHERE id > ?";
        $pdo = $this->ensureActiveQueriesErrorCanHappen();
        try {
            $this->isolateTracer(function () use ($query, $pdo) {
                $stmt = $pdo->prepare($query);
                $stmt->execute([10]);
                $stmt->fetch();

                $stmt2 = $pdo->prepare($query);
                $stmt2->execute([10]);
                $stmt2->fetch();
            });

            $this->fail("Expected exception PDOException not thrown");
        } catch (\PDOException $ex) {
            // ignore
        }
    }

    public function testPDOStatementError()
    {
        $query = "WRONG QUERY";
        $traces = $this->isolateTracer(function () use ($query) {
            try {
                $pdo = $this->pdoInstance();
                $stmt = $pdo->prepare($query);
                $stmt->execute([1]);
                $stmt->fetchAll();
                $stmt->closeCursor();
                $stmt = null;
                $pdo = null;
            } catch (\PDOException $ex) {
            }
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.prepare', 'PDO', 'sql', "WRONG QUERY")
                ->withExactTags(array_merge($this->baseTags(), [])),
            SpanAssertion::build('PDOStatement.execute', 'PDO', 'sql', "WRONG QUERY")
                ->setTraceAnalyticsCandidate()
                ->setError('PDOStatement error', 'SQL error: 42000. Driver error: 1064')
                    ->withExactTags(array_merge($this->baseTags(), [
                        'db.rowcount' => 0,
                    ])),
        ]);
    }

    public function testPDOStatementException()
    {
        $query = "WRONG QUERY";
        $traces = $this->isolateTracer(function () use ($query) {
            try {
                $pdo = $this->pdoInstance();
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->prepare($query);
                $stmt->execute([1]);
                $stmt->fetchAll();
                $stmt->closeCursor();
                $stmt = null;
                $pdo = null;
            } catch (\PDOException $ex) {
            }
        });
        $this->assertSpans($traces, [
            SpanAssertion::exists('PDO.__construct'),
            SpanAssertion::build('PDO.prepare', 'PDO', 'sql', "WRONG QUERY")
                ->withExactTags(array_merge($this->baseTags(), [])),
            SpanAssertion::build('PDOStatement.execute', 'PDO', 'sql', "WRONG QUERY")
                ->setTraceAnalyticsCandidate()
                ->setError('PDOException', 'Sql error')
                ->withExactTags($this->baseTags()),
        ]);
    }

    public function testLimitedTracerPDO()
    {
        $query = "SELECT * FROM tests WHERE id = ?";
        $traces = $this->isolateLimitedTracer(function () use ($query) {
            $pdo = $this->pdoInstance();
            $stmt = $pdo->prepare($query);
            $stmt->execute([1]);
            $results = $stmt->fetchAll();
            $this->assertEquals('Tom', $results[0]['name']);
            $stmt->closeCursor();
            $stmt = null;
            $pdo = null;
        });

        $this->assertEmpty($traces);
    }

    private function pdoInstance($opts = null)
    {
        $instance =  new \PDO($this->mysqlDns(), MYSQL_USER, MYSQL_PASSWORD, $opts);

        return $instance;
    }

    private function ensureActiveQueriesErrorCanHappen()
    {
        $opts = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
        );

        $pdo = $this->pdoInstance($opts);

        $this->isolateTracer(function () use ($pdo) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO tests (name) VALUES (?)");

            for ($i = 0; $i < 1000; $i++) {
                $stmt->execute(['Jerry']);
            }
            $pdo->commit();
        });
        return $pdo;
    }

    private function setUpDatabase()
    {
        $this->isolateTracer(function () {
            $pdo = $this->pdoInstance();
            $pdo->beginTransaction();
            $pdo->exec("
                CREATE TABLE tests (
                    id integer not null primary key AUTO_INCREMENT,
                    name varchar(100)
                )
            ");
            $pdo->exec("INSERT INTO tests (id, name) VALUES (1, 'Tom')");

            $pdo->commit();
            $pdo = null;
        });
    }

    private function clearDatabase()
    {
        $this->isolateTracer(function () {
            $pdo = $this->pdoInstance();
            $pdo->beginTransaction();
            $pdo->exec("DROP TABLE tests");
            $pdo->commit();
            $pdo = null;
        });
    }

    public function mysqlDns()
    {
        return $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE;
    }

    private function baseTags()
    {
        return [
            'db.engine' => 'mysql',
            'out.host' => MYSQL_HOST,
            'db.name' => MYSQL_DATABASE,
            'db.user' => MYSQL_USER,
        ];
    }
}
