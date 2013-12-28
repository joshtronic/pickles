<?php

class DisplayTest extends PHPUnit_Framework_TestCase
{
	private $display, $shared_templates;

	private $child_html = '<div>child template</div>';

	protected function setUp()
	{
		parent::setUp();

		$this->shared_templates = SITE_TEMPLATE_PATH . '__shared/';

		if (!file_exists($this->shared_templates))
		{
			mkdir($this->shared_templates, 0644, true);
		}

		$_SERVER['REQUEST_URI'] = '/test';
		$_REQUEST['request']    = 'test';

		$this->display         = new Display();
		$this->display->module = [
			'pickles' => [
				'yummy'  => 'gherkin',
				'delish' => 'kosher dill',
				'yucky'  => 'bread & butter'
			]
		];
	}

	protected function tearDown()
	{
		unlink(SITE_TEMPLATE_PATH . 'test.phtml');
		unlink($this->shared_templates . 'index.phtml');
	}

	public function testInvalidReturnType()
	{
		$this->display->return = 'invalid';
		$this->assertEquals('Invalid return type.', $this->display->render());
	}

	public function testPHPSESSID()
	{
		$request_uri             = $_SERVER['REQUEST_URI'];
		$_SERVER['REQUEST_URI'] .= '?PHPSESSID=session_id';
		$return                  = $this->display->render();

		$this->assertTrue(in_array('Location: ' . $request_uri, xdebug_get_headers()));
		$this->assertEquals('Requested URI contains PHPSESSID, redirecting.', $return);
	}

	public function testNoParentTemplate()
	{
		file_put_contents(SITE_TEMPLATE_PATH . 'test.phtml', $this->child_html);

		$this->assertEquals($this->child_html, $this->display->render());
	}

	public function testRenderTemplate()
	{
		file_put_contents(SITE_TEMPLATE_PATH . 'test.phtml', $this->child_html);

		$child = '<?php require $this->template; ?>' . "\n";

		$html = <<<HTML
<!doctype html>
<html>
	<body>
		<!-- BuySellAds Unstripped Comment -->
		<h1>parent template</h1>
		{$child}
	</body>
</html>
HTML;

		file_put_contents($this->shared_templates . 'index.phtml', $html);

		$html = str_replace($child, $this->child_html, $html);
		$html = preg_replace(['/^[\s]+/m', '/<!--(?:(?!BuySellAds).)+-->/U'], '', $html);

		$this->assertEquals($html, $this->display->render());
	}

	public function testRenderJSON()
	{
		$this->assertEquals(
			'{"pickles":{"yummy":"gherkin","delish":"kosher dill","yucky":"bread & butter"}}',
			$this->display->render()
		);
	}

	public function testRenderJSONPrettyPrint()
	{
		$_REQUEST['pretty'] = 'true';

		$pretty_json = <<<JSON
{
    "pickles": {
        "yummy": "gherkin",
        "delish": "kosher dill",
        "yucky": "bread & butter"
    }
}
JSON;

		$this->assertEquals($pretty_json, $this->display->render());
	}

	public function testRenderXML()
	{
		$this->display->return = ['template', 'xml'];
		$this->assertEquals(
			'<yummy>gherkin</yummy><delish>kosher dill</delish><yucky><![CDATA[bread & butter]]></yucky>',
			$this->display->render()
		);
	}

	public function testRenderXMLPrettyPrint()
	{
		$_REQUEST['pretty'] = 'true';

		$pretty_xml = <<<XML
<yummy>gherkin</yummy>
<delish>kosher dill</delish>
<yucky><![CDATA[bread & butter]]></yucky>

XML;

		$this->display->return = ['template', 'xml'];
		$this->assertEquals($pretty_xml, $this->display->render());
	}

	/*
	public function testRenderRSS()
	{
		$this->fail('Not yet implemented.');
	}

	public function testRenderRSSPrettyPrint()
	{
		$this->fail('Not yet implemented.');
	}
	*/
}

?>
