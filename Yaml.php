<?php

require __DIR__.'/vendor/autoload.php';

class Yaml
{
  protected $lines;

  /**
   * @param $input string
   */
  public function __construct($input)
  {
    $this->lines = $this->getLines($input);
  }

  protected function getLines($input)
  {
    return array_map(function($line) {
      return new Line(rtrim($line, "\r"));
    }, explode("\n", $input));
  }

  /**
   * @param $lines array
   */
  public function getBlocks(array $lines = [])
  {
    if (empty($lines)) {
      $lines = $this->lines;
    }

    $currentLineType = null;
    $currentScalarStyle = null; // '|>'
    $currentIndent = 0;
    $currentBlock = 0;

    $blocks = [];

    foreach ($lines as $line) {

      if ($line->isEmpty() || $line->isComment()) {
        continue;
      }

      // if (! ($currentType === 'scalar' && $currentScalarStyle !== null)) {
      //   if ($line->isEmpty() && $line->isComment()) {
      //     continue;
      //   }
      // }

      if ($currentLineType == null) {
        $currentLineType = $line->getLineType();
      }

      if ($currentIndent === $line->getIndent()) {
        if ($currentLineType !== $line->getLineType()) {
          throw new Exception("Error Processing Request", 1);
        }
        unset($currentBlock);
        $currentBlock = ['key' => $line->getKey(), 'type' => $line->getValueType(), 'value' => $line->getValue()];
        $blocks[] = &$currentBlock;
      } else {
        $currentBlock['blocks'][] = $line->substr(2);
      }
    }

    foreach ($blocks as $key => &$item) {
      if (isset($item['blocks'])) {
        $item['blocks'] = $this->getBlocks($item['blocks']);
      }
    }

    return $blocks;
  }

  public function toArray()
  {
    return [];
  }

  public static function parse($input)
  {
    return (new static($input))->toArray();
  }
}

// Line
class Line
{
  protected $line;

  function __construct($line = null)
  {
    $this->line = $line;
  }

  public function substr(...$args)
  {
    return new static(substr($this->line, ...$args));
  }

  public function isEmpty()
  {
    return empty(trim($this->line, ' '));
  }

  public function isComment()
  {
    return strpos(ltrim($this->line, ' '), '#') === 0;
  }

  public function getLineType()
  {
    return 'map';
  }

  public function getValueType()
  {
    return 'scalar';
  }

  public function getIndent()
  {
    return strlen($this->line) - strlen(ltrim($this->line, ' '));
  }

  public function getKey()
  {
    return $this->line;
  }

  public function getValue()
  {
    
  }
}


$input = "
key0: value0

key1: value1
  value1
  value1
  value1
  value1
  value1

key2:
  value2

key3:
  key_3_1: 
    value_3_1

key4:
  key_4_1: 
    key_4_1_1: 
      value_4_1
";

$yaml = new Yaml($input);

s($yaml->getBlocks());
// s($yaml->toArray());

s(Yaml::parse($input));

