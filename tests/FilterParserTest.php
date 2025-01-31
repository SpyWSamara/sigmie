<?php

declare(strict_types=1);

namespace Sigmie\Tests;

use RuntimeException;
use Sigmie\Document\Document;
use Sigmie\Mappings\NewProperties;
use Sigmie\Mappings\Properties;
use Sigmie\Parse\FilterParser;
use Sigmie\Testing\TestCase;

class FilterParserTest extends TestCase
{
    /**
     * @test
     */
    public function exceptions()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;

        $props = $blueprint();

        $this->expectException(RuntimeException::class);

        $parser = new FilterParser($props);

        $boolean = $parser->parse('category:"sports" AND is:active OR name:foo');
    }

    /**
     * @test
     */
    public function foo()
    {
        $indexName = uniqid();

        $blueprint = new NewProperties;
        $blueprint->keyword('category');
        $blueprint->bool('active');
        $blueprint->number('stock')->integer();

        $index = $this->sigmie->newIndex($indexName)
        ->properties($blueprint)
        ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document(['category' => 'comendy', 'stock' => 10, 'active' => false]),
            new Document(['category' => 'action', 'stock' => 58, 'active' => true]),
            new Document(['category' => 'horror', 'stock' => 0, 'active' => true]),
            new Document(['category' => 'horror', 'stock' => 10, 'active' => false]),
            new Document(['category' => 'romance', 'stock' => 10, 'active' => false]),
            new Document(['category' => 'drama', 'stock' => 10, 'active' => true]),
            new Document(['category' => 'sports', 'stock' => 10, 'active' => true]),
        ];

        $index->merge($docs);

        $props = $blueprint();

        $parser = new FilterParser($props);

        $query = $parser->parse('is:active AND NOT (category:"drama" OR category:"horror")');

        $res = $this->sigmie->query($indexName, $query)->get();

        $this->assertCount(2, $res->json('hits.hits'));

        $query = $parser->parse("is:active AND NOT category:'drama'");

        $res = $this->sigmie->query($indexName, $query)->get();

        $this->assertCount(3, $res->json('hits.hits'));

        $query = $parser->parse('is:active AND stock>0');

        $res = $this->sigmie->query($indexName, $query)->get();

        $this->assertCount(3, $res->json('hits.hits'));

        $query = $parser->parse('is:active');

        $res = $this->sigmie->query($indexName, $query)->get();

        $this->assertCount(4, $res->json('hits.hits'));

        $query = $parser->parse('is:active AND stock>0 AND (category:"action" OR category:"horror")');

        $res = $this->sigmie->query($indexName, $query)->get();

        $this->assertCount(1, $res->json('hits.hits'));

        $query = $parser->parse('(category:"action" OR category:"horror") AND is:active AND stock>0');

        $res = $this->sigmie->query($indexName, $query)->get();

        $this->assertCount(1, $res->json('hits.hits'));

        $query = $parser->parse('is:active AND (category:"action" OR category:"horror") AND stock>0');

        $res = $this->sigmie->query($indexName, $query)->get();

        $this->assertCount(1, $res->json('hits.hits'));
    }

    /**
     * @test
     */
    public function term_long_string_filter_with_single_quotes()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->text('name')->unstructuredText()->keyword();
        $blueprint->keyword('category');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse('category:\'crime & drama\' OR category:\'crime OR | AND | AND NOT sports\'');

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document([
                'category' => 'sports',
            ]),
            new Document([
                'category' => 'sports',
            ]),
            new Document([
                'category' => 'crime & drama',
            ]),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        foreach ($res->json('hits.hits') as $index => $data) {
            $source = $data['_source'];
            $this->assertTrue($source['category'] === 'crime & drama');
        }
    }

    /**
     * @test
     */
    public function term_long_string_filter()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->text('name')->unstructuredText()->keyword();
        $blueprint->keyword('category');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse('category:"crime & drama" OR category:"crime OR | AND | AND NOT sports"');

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document([
                'category' => 'sports',
            ]),
            new Document([
                'category' => 'sports',
            ]),
            new Document([
                'category' => 'crime & drama',
            ]),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        foreach ($res->json('hits.hits') as $index => $data) {
            $source = $data['_source'];
            $this->assertTrue($source['category'] === 'crime & drama');
        }
    }

    /**
     * @test
     */
    public function term_filter()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->text('name')->unstructuredText()->keyword();
        $blueprint->keyword('category');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse('category:"sports" AND NOT name:"Adidas"');

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document([
                'category' => 'sports',
                'name' => 'Nike',
            ]),
            new Document([
                'category' => 'sports',
                'name' => 'Adidas',
            ]),
            new Document([
                'category' => 'sports',
                'name' => 'Nike',
            ]),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        foreach ($res->json('hits.hits') as $index => $data) {
            $source = $data['_source'];
            $this->assertTrue($source['name'] !== 'Adidas');
            $this->assertTrue($source['category'] === 'sports');
        }
    }

    /**
     * @test
     */
    public function is_not_filter()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->bool('active');
        $blueprint->text('name')->unstructuredText();
        $blueprint->text('category');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse('is_not:active');

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document([
                'category' => 'Disney',
                'name' => 'Pluto',
                'active' => true,
            ]),
            new Document([
                'category' => 'Disney',
                'name' => 'Arthur',
                'active' => true,
            ]),
            new Document([
                'category' => 'Disney',
                'name' => 'Dory',
                'active' => false,
            ]),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        $hits = $res->json('hits.hits');

        $this->assertCount(1, $hits);
        $this->assertFalse($hits[0]['_source']['active']);
    }

    /**
     * @test
     */
    public function is_filter()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->bool('active');
        $blueprint->text('name')->unstructuredText();
        $blueprint->text('category');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse('is:active');

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document([
                'category' => 'Disney',
                'name' => 'Pluto',
                'active' => true,
            ]),
            new Document([
                'category' => 'Disney',
                'name' => 'Arthur',
                'active' => true,
            ]),
            new Document([
                'category' => 'Disney',
                'name' => 'Dory',
                'active' => false,
            ]),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        $hits = $res->json('hits.hits');

        $this->assertCount(2, $hits);
        $this->assertTrue($hits[0]['_source']['active']);
        $this->assertTrue($hits[1]['_source']['active']);
    }

    /**
     * @test
     */
    public function date_single_quotes_range()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->date('created_at');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse("created_at>='2023-05-01' AND created_at<='2023-08-01'");

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document(
                ['created_at' => '2023-04-07T00:00:00.000000Z'],
            ),
            new Document(
                ['created_at' => '2023-05-07T00:00:00.000000Z'],
            ),
            new Document(
                ['created_at' => '2023-05-07T00:00:00.000000Z'],
            ),
            new Document(
                ['created_at' => '2023-09-07T00:00:00.000000Z'],
            ),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        $hits = $res->json('hits.hits');

        $this->assertCount(2, $hits);
    }

    /**
     * @test
     */
    public function date_double_quotes_range()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->date('created_at');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse('created_at>="2023-05-01" AND created_at<="2023-08-01"');

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document(
                ['created_at' => '2023-04-07T00:00:00.000000Z'],
            ),
            new Document(
                ['created_at' => '2023-05-07T00:00:00.000000Z'],
            ),
            new Document(
                ['created_at' => '2023-05-07T00:00:00.000000Z'],
            ),
            new Document(
                ['created_at' => '2023-09-07T00:00:00.000000Z'],
            ),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        $hits = $res->json('hits.hits');

        $this->assertCount(2, $hits);
    }

    /**
     * @test
     */
    public function not()
    {
        $mappings = new Properties();

        $blueprint = new NewProperties;
        $blueprint->category('category');

        $props = $blueprint();
        $parser = new FilterParser($props);
        $boolean = $parser->parse('NOT category:"Sports"');

        $indexName = uniqid();
        $index = $this->sigmie
            ->newIndex($indexName)
            ->properties($blueprint)
            ->create();

        $index = $this->sigmie->collect($indexName, true);

        $docs = [
            new Document([
                'category' => 'Drama',
            ]),
            new Document([
                'category' => 'Sports',
            ]),
            new Document([
                'category' => 'Action',
            ]),
        ];

        $index->merge($docs);

        $res = $this->sigmie->query($indexName, $boolean)->get();

        $hits = $res->json('hits.hits');

        $this->assertCount(2, $hits);
        $this->assertNotEquals('Sports', $hits[0]['_source']['category']);
        $this->assertNotEquals('Sports', $hits[1]['_source']['category']);
    }
}
