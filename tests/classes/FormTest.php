<?php

class FormTest extends PHPUnit_Framework_TestCase
{
	private $form;
	private $options;

	public function setUp()
	{
		$this->form = Form::getInstance();
		$this->options = [
			'1' => 'one',
			'2' => 'two',
			'3' => 'three',
		];
	}

	public function testGetInstance()
	{
		$this->assertInstanceOf('Form', $this->form);
	}

	public function testInput()
	{
		$this->assertEquals(
			'<input type="text" name="name" id="name">',
			$this->form->input('name')
		);
	}

	public function testInputWithClasses()
	{
		$this->assertEquals(
			'<input type="text" name="name" id="name" class="foo bar">',
			$this->form->input('name', null, 'foo bar')
		);
	}

	public function testInputWithAdditional()
	{
		$this->assertEquals(
			'<input type="text" name="name" id="name" test="ing">',
			$this->form->input('name', null, null, 'test="ing"')
		);
	}

	public function testHidden()
	{
		$this->assertEquals(
			'<input type="hidden" name="name" id="name">',
			$this->form->hidden('name')
		);
	}

	public function testHiddenInput()
	{
		$this->assertEquals(
			'<input type="hidden" name="name" id="name">',
			$this->form->hiddenInput('name')
		);
	}

	public function testPassword()
	{
		$this->assertEquals(
			'<input type="password" name="name" id="name">',
			$this->form->password('name')
		);
	}

	public function testPasswordInput()
	{
		$this->assertEquals(
			'<input type="password" name="name" id="name">',
			$this->form->passwordInput('name')
		);
	}

	public function testSubmit()
	{
		$this->assertEquals(
			'<input type="submit" name="name" id="name">',
			$this->form->submit('name')
		);
	}

	public function testSubmitInput()
	{
		$this->assertEquals(
			'<input type="submit" name="name" id="name">',
			$this->form->submitInput('name')
		);
	}

	public function testSecurity()
	{
		$this->assertEquals(
			'<input type="hidden" name="security_hash" id="security_hash" value="27c4f7de78e6636f9bef0e184a9e6955a04e5441">',
			$this->form->security('secret')
		);
	}

	public function testSecurityInput()
	{
		$this->assertEquals(
			'<input type="hidden" name="security_hash" id="security_hash" value="27c4f7de78e6636f9bef0e184a9e6955a04e5441">',
			$this->form->securityInput('secret')
		);
	}

	public function testCheckbox()
	{
		$this->assertEquals(
			'<input type="checkbox" name="name" id="name">',
			$this->form->checkbox('name')
		);
	}

	public function testCheckboxChecked()
	{
		$this->assertEquals(
			'<input type="checkbox" name="name" id="name" checked="checked">',
			$this->form->checkbox('name', null, true)
		);
	}

	public function testRadio()
	{
		$this->assertEquals(
			'<input type="radio" name="name" id="name">',
			$this->form->radio('name')
		);
	}

	public function testTextarea()
	{
		$this->assertEquals(
			'<textarea name="name" id="name"></textarea>',
			$this->form->textarea('name')
		);
	}

	public function testTextareaWithClasses()
	{
		$this->assertEquals(
			'<textarea name="name" id="name" class="foo bar"></textarea>',
			$this->form->textarea('name', null, 'foo bar')
		);
	}

	public function testTextareaWithAdditional()
	{
		$this->assertEquals(
			'<textarea name="name" id="name" test="ing"></textarea>',
			$this->form->textarea('name', null, null, 'test="ing"')
		);
	}

	public function testSelect()
	{
		$this->assertEquals(
			'<select id="name" name="name"><option label="one" value="1">one</option><option label="two" value="2">two</option><option label="three" value="3">three</option></select>',
			$this->form->select('name', $this->options)
		);
	}

	public function testSelectWithClasses()
	{
		$this->assertEquals(
			'<select id="name" name="name" class="foo bar"><option label="one" value="1">one</option><option label="two" value="2">two</option><option label="three" value="3">three</option></select>',
			$this->form->select('name', $this->options, null, 'foo bar')
		);
	}

	public function testSelectWithAdditional()
	{
		$this->assertEquals(
			'<select id="name" name="name" test="ing"><option label="one" value="1">one</option><option label="two" value="2">two</option><option label="three" value="3">three</option></select>',
			$this->form->select('name', $this->options, null, null, 'test="ing"')
		);
	}

	public function testOptions()
	{
		$this->assertEquals(
			'<option label="one" value="1">one</option><option label="two" value="2">two</option><option label="three" value="3">three</option>',
			$this->form->options($this->options)
		);
	}

	public function testOptionsWithMissingSelected()
	{
		$this->assertEquals(
			'<option label="one" value="1">one</option><option label="two" value="2">two</option><option label="three" value="3">three</option><option value="4" selected="selected" class="error">4</option>',
			$this->form->options($this->options, 4)
		);
	}

	public function testOptionsOptGroup()
	{
		$this->assertEquals(
			'<optgroup label="group"><option label="one" value="1">one</option><option label="two" value="2">two</option><option label="three" value="3">three</option></optgroup>',
			$this->form->options(['group' => $this->options])
		);
	}

	public function testOptionsOptGroupSelected()
	{
		$this->assertEquals(
			'<optgroup label="group"><option label="one" value="1">one</option><option label="two" value="2" selected="selected">two</option><option label="three" value="3">three</option></optgroup>',
			$this->form->options(['group' => $this->options], 2)
		);
	}

	public function testStateSelect()
	{
		$this->assertRegExp(
			'/^<select id="state" name="state"><option label="-- Select State --" value="">-- Select State --<\/option>(<option label=".+" value="[A-Z]{2}">.+<\/option>)+<\/select>$/',
			$this->form->stateSelect()
		);
	}

	public function testDateSelect()
	{
		$this->assertEquals(
			'<select id="date[month]" name="date[month]"><option label="Month" value="">Month</option><option label="January" value="01">January</option><option label="February" value="02">February</option><option label="March" value="03">March</option><option label="April" value="04">April</option><option label="May" value="05">May</option><option label="June" value="06">June</option><option label="July" value="07">July</option><option label="August" value="08">August</option><option label="September" value="09">September</option><option label="October" value="10">October</option><option label="November" value="11">November</option><option label="December" value="12">December</option></select> <select id="date[day]" name="date[day]"><option label="Day" value="">Day</option><option label="1" value="01">1</option><option label="2" value="02">2</option><option label="3" value="03">3</option><option label="4" value="04">4</option><option label="5" value="05">5</option><option label="6" value="06">6</option><option label="7" value="07">7</option><option label="8" value="08">8</option><option label="9" value="09">9</option><option label="10" value="10">10</option><option label="11" value="11">11</option><option label="12" value="12">12</option><option label="13" value="13">13</option><option label="14" value="14">14</option><option label="15" value="15">15</option><option label="16" value="16">16</option><option label="17" value="17">17</option><option label="18" value="18">18</option><option label="19" value="19">19</option><option label="20" value="20">20</option><option label="21" value="21">21</option><option label="22" value="22">22</option><option label="23" value="23">23</option><option label="24" value="24">24</option><option label="25" value="25">25</option><option label="26" value="26">26</option><option label="27" value="27">27</option><option label="28" value="28">28</option><option label="29" value="29">29</option><option label="30" value="30">30</option><option label="31" value="31">31</option></select> <select id="date[year]" name="date[year]"><option label="Year" value="">Year</option></select>',
			$this->form->dateSelect()
		);
	}

	public function testDateSelectWithDate()
	{
		$this->assertEquals(
			'<select id="date[month]" name="date[month]"><option label="Month" value="">Month</option><option label="January" value="01">January</option><option label="February" value="02" selected="selected">February</option><option label="March" value="03">March</option><option label="April" value="04">April</option><option label="May" value="05">May</option><option label="June" value="06">June</option><option label="July" value="07">July</option><option label="August" value="08">August</option><option label="September" value="09">September</option><option label="October" value="10">October</option><option label="November" value="11">November</option><option label="December" value="12">December</option></select> <select id="date[day]" name="date[day]"><option label="Day" value="">Day</option><option label="1" value="01">1</option><option label="2" value="02">2</option><option label="3" value="03">3</option><option label="4" value="04">4</option><option label="5" value="05">5</option><option label="6" value="06">6</option><option label="7" value="07">7</option><option label="8" value="08">8</option><option label="9" value="09">9</option><option label="10" value="10">10</option><option label="11" value="11">11</option><option label="12" value="12">12</option><option label="13" value="13">13</option><option label="14" value="14">14</option><option label="15" value="15">15</option><option label="16" value="16">16</option><option label="17" value="17">17</option><option label="18" value="18">18</option><option label="19" value="19">19</option><option label="20" value="20">20</option><option label="21" value="21">21</option><option label="22" value="22">22</option><option label="23" value="23" selected="selected">23</option><option label="24" value="24">24</option><option label="25" value="25">25</option><option label="26" value="26">26</option><option label="27" value="27">27</option><option label="28" value="28">28</option><option label="29" value="29">29</option><option label="30" value="30">30</option><option label="31" value="31">31</option></select> <select id="date[year]" name="date[year]"><option label="Year" value="">Year</option><option label="1990" value="1990">1990</option><option label="1989" value="1989">1989</option><option label="1988" value="1988">1988</option><option label="1987" value="1987">1987</option><option label="1986" value="1986">1986</option><option label="1985" value="1985">1985</option><option label="1984" value="1984">1984</option><option label="1983" value="1983">1983</option><option label="1982" value="1982">1982</option><option label="1981" value="1981" selected="selected">1981</option><option label="1980" value="1980">1980</option></select>',
			$this->form->dateSelect('date', '1981-02-23', null, null, 1990, 1980)
		);
	}

	public function testDOBSelect()
	{
		$this->assertEquals(
			'<select id="dob[month]" name="dob[month]"><option label="Month" value="">Month</option><option label="January" value="01">January</option><option label="February" value="02">February</option><option label="March" value="03">March</option><option label="April" value="04">April</option><option label="May" value="05">May</option><option label="June" value="06">June</option><option label="July" value="07">July</option><option label="August" value="08">August</option><option label="September" value="09">September</option><option label="October" value="10">October</option><option label="November" value="11">November</option><option label="December" value="12">December</option></select> <select id="dob[day]" name="dob[day]"><option label="Day" value="">Day</option><option label="1" value="01">1</option><option label="2" value="02">2</option><option label="3" value="03">3</option><option label="4" value="04">4</option><option label="5" value="05">5</option><option label="6" value="06">6</option><option label="7" value="07">7</option><option label="8" value="08">8</option><option label="9" value="09">9</option><option label="10" value="10">10</option><option label="11" value="11">11</option><option label="12" value="12">12</option><option label="13" value="13">13</option><option label="14" value="14">14</option><option label="15" value="15">15</option><option label="16" value="16">16</option><option label="17" value="17">17</option><option label="18" value="18">18</option><option label="19" value="19">19</option><option label="20" value="20">20</option><option label="21" value="21">21</option><option label="22" value="22">22</option><option label="23" value="23">23</option><option label="24" value="24">24</option><option label="25" value="25">25</option><option label="26" value="26">26</option><option label="27" value="27">27</option><option label="28" value="28">28</option><option label="29" value="29">29</option><option label="30" value="30">30</option><option label="31" value="31">31</option></select> <select id="dob[year]" name="dob[year]"><option label="Year" value="">Year</option><option label="2014" value="2014">2014</option><option label="2013" value="2013">2013</option><option label="2012" value="2012">2012</option><option label="2011" value="2011">2011</option><option label="2010" value="2010">2010</option><option label="2009" value="2009">2009</option><option label="2008" value="2008">2008</option><option label="2007" value="2007">2007</option><option label="2006" value="2006">2006</option><option label="2005" value="2005">2005</option><option label="2004" value="2004">2004</option><option label="2003" value="2003">2003</option><option label="2002" value="2002">2002</option><option label="2001" value="2001">2001</option><option label="2000" value="2000">2000</option><option label="1999" value="1999">1999</option><option label="1998" value="1998">1998</option><option label="1997" value="1997">1997</option><option label="1996" value="1996">1996</option><option label="1995" value="1995">1995</option><option label="1994" value="1994">1994</option><option label="1993" value="1993">1993</option><option label="1992" value="1992">1992</option><option label="1991" value="1991">1991</option><option label="1990" value="1990">1990</option><option label="1989" value="1989">1989</option><option label="1988" value="1988">1988</option><option label="1987" value="1987">1987</option><option label="1986" value="1986">1986</option><option label="1985" value="1985">1985</option><option label="1984" value="1984">1984</option><option label="1983" value="1983">1983</option><option label="1982" value="1982">1982</option><option label="1981" value="1981">1981</option><option label="1980" value="1980">1980</option><option label="1979" value="1979">1979</option><option label="1978" value="1978">1978</option><option label="1977" value="1977">1977</option><option label="1976" value="1976">1976</option><option label="1975" value="1975">1975</option><option label="1974" value="1974">1974</option><option label="1973" value="1973">1973</option><option label="1972" value="1972">1972</option><option label="1971" value="1971">1971</option><option label="1970" value="1970">1970</option><option label="1969" value="1969">1969</option><option label="1968" value="1968">1968</option><option label="1967" value="1967">1967</option><option label="1966" value="1966">1966</option><option label="1965" value="1965">1965</option><option label="1964" value="1964">1964</option><option label="1963" value="1963">1963</option><option label="1962" value="1962">1962</option><option label="1961" value="1961">1961</option><option label="1960" value="1960">1960</option><option label="1959" value="1959">1959</option><option label="1958" value="1958">1958</option><option label="1957" value="1957">1957</option><option label="1956" value="1956">1956</option><option label="1955" value="1955">1955</option><option label="1954" value="1954">1954</option><option label="1953" value="1953">1953</option><option label="1952" value="1952">1952</option><option label="1951" value="1951">1951</option><option label="1950" value="1950">1950</option><option label="1949" value="1949">1949</option><option label="1948" value="1948">1948</option><option label="1947" value="1947">1947</option><option label="1946" value="1946">1946</option><option label="1945" value="1945">1945</option><option label="1944" value="1944">1944</option><option label="1943" value="1943">1943</option><option label="1942" value="1942">1942</option><option label="1941" value="1941">1941</option><option label="1940" value="1940">1940</option><option label="1939" value="1939">1939</option><option label="1938" value="1938">1938</option><option label="1937" value="1937">1937</option><option label="1936" value="1936">1936</option><option label="1935" value="1935">1935</option><option label="1934" value="1934">1934</option><option label="1933" value="1933">1933</option><option label="1932" value="1932">1932</option><option label="1931" value="1931">1931</option><option label="1930" value="1930">1930</option><option label="1929" value="1929">1929</option><option label="1928" value="1928">1928</option><option label="1927" value="1927">1927</option><option label="1926" value="1926">1926</option><option label="1925" value="1925">1925</option><option label="1924" value="1924">1924</option><option label="1923" value="1923">1923</option><option label="1922" value="1922">1922</option><option label="1921" value="1921">1921</option><option label="1920" value="1920">1920</option><option label="1919" value="1919">1919</option><option label="1918" value="1918">1918</option><option label="1917" value="1917">1917</option><option label="1916" value="1916">1916</option><option label="1915" value="1915">1915</option><option label="1914" value="1914">1914</option><option label="1913" value="1913">1913</option><option label="1912" value="1912">1912</option><option label="1911" value="1911">1911</option><option label="1910" value="1910">1910</option><option label="1909" value="1909">1909</option><option label="1908" value="1908">1908</option><option label="1907" value="1907">1907</option><option label="1906" value="1906">1906</option><option label="1905" value="1905">1905</option><option label="1904" value="1904">1904</option><option label="1903" value="1903">1903</option><option label="1902" value="1902">1902</option><option label="1901" value="1901">1901</option><option label="1900" value="1900">1900</option><option label="1899" value="1899">1899</option><option label="1898" value="1898">1898</option><option label="1897" value="1897">1897</option><option label="1896" value="1896">1896</option></select>',
			$this->form->dobSelect()
		);
	}

	public function testPolarSelect()
	{
		$this->assertEquals(
			'<select id="decision" name="decision"><option label="Yes" value="1">Yes</option><option label="No" value="0" selected="selected">No</option></select>',
			$this->form->polarSelect()
		);
	}

	public function testPhoneInput()
	{
		$this->assertEquals(
			'<input type="input" name="phone[area_code]" id="phone[area_code]" value="" minlength="3" maxlength="3" class="digits"> <input type="input" name="phone[prefix]" id="phone[prefix]" value="" minlength="3" maxlength="3" class="digits"> <input type="input" name="phone[line_number]" id="phone[line_number]" value="" minlength="4" maxlength="4" class="digits">',
			$this->form->phoneInput()
		);
	}

	public function testPhoneInputWithValue()
	{
		$this->assertEquals(
			'<input type="input" name="phone[area_code]" id="phone[area_code]" value="302" minlength="3" maxlength="3" class="digits"> <input type="input" name="phone[prefix]" id="phone[prefix]" value="555" minlength="3" maxlength="3" class="digits"> <input type="input" name="phone[line_number]" id="phone[line_number]" value="0134" minlength="4" maxlength="4" class="digits">',
			$this->form->phoneInput('phone', '3025550134')
		);
	}

	public function testPhoneInputWithValueWithDashes()
	{
		$this->assertEquals(
			'<input type="input" name="phone[area_code]" id="phone[area_code]" value="302" minlength="3" maxlength="3" class="digits"> <input type="input" name="phone[prefix]" id="phone[prefix]" value="555" minlength="3" maxlength="3" class="digits"> <input type="input" name="phone[line_number]" id="phone[line_number]" value="0134" minlength="4" maxlength="4" class="digits">',
			$this->form->phoneInput('phone', '302-555-0134')
		);
	}

	public function testPhoneInputWithClasses()
	{
		$this->assertEquals(
			'<input type="input" name="phone[area_code]" id="phone[area_code]" value="" minlength="3" maxlength="3" class="digits foo bar"> <input type="input" name="phone[prefix]" id="phone[prefix]" value="" minlength="3" maxlength="3" class="digits foo bar"> <input type="input" name="phone[line_number]" id="phone[line_number]" value="" minlength="4" maxlength="4" class="digits foo bar">',
			$this->form->phoneInput('phone', null, 'foo bar')
		);
	}

	public function testPhoneInputWithAdditional()
	{
		$this->assertEquals(
			'<input type="input" name="phone[area_code]" id="phone[area_code]" value="" minlength="3" maxlength="3" test="ing" class="digits"> <input type="input" name="phone[prefix]" id="phone[prefix]" value="" minlength="3" maxlength="3" test="ing" class="digits"> <input type="input" name="phone[line_number]" id="phone[line_number]" value="" minlength="4" maxlength="4" test="ing" class="digits">',
			$this->form->phoneInput('phone', null, null, 'test="ing"')
		);
	}
}

?>
