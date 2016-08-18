<?php
namespace DDPro\Admin\Tests\Fields;

use Mockery as m;

class FileTest extends \PHPUnit_Framework_TestCase {

    /**
     * The Validator mock
     *
     * @var Mockery
     */
    protected $validator;

    /**
     * The Config mock
     *
     * @var Mockery
     */
    protected $config;

    /**
     * The DB mock
     *
     * @var Mockery
     */
    protected $db;

    /**
     * The FieldFactory mock
     *
     * @var Mockery
     */
    protected $field;

    /**
     * Set up function
     */
    public function setUp()
    {
        $this->validator = m::mock('DDPro\Admin\Validator');
        $this->config = m::mock('DDPro\Admin\Config\Model\Config');
        $this->db = m::mock('Illuminate\Database\DatabaseManager');
        $options = array('field_name' => 'field', 'type' => 'enum');
        $this->field = m::mock('DDPro\Admin\Fields\File', array($this->validator, $this->config, $this->db, $options))->makePartial();
    }

    /**
     * Tear down function
     */
    public function tearDown()
    {
        m::close();
    }

    public function testBuild()
    {
        $url = m::mock('Illuminate\Routing\UrlGenerator');
        $url->shouldReceive('route')->once();
        $this->validator->shouldReceive('arrayGet')->times(3)
                        ->shouldReceive('getUrlInstance')->once()->andReturn($url);
        $this->config->shouldReceive('getType')->once()
                        ->shouldReceive('getOption')->once();
        $this->field->build();
    }

}
