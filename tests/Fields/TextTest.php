<?php
namespace DDPro\Admin\Tests\Fields;

use Mockery as m;

class TextTest extends \PHPUnit_Framework_TestCase {

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
        $options = array('field_name' => 'field', 'type' => 'text');
        $this->field = m::mock('DDPro\Admin\Fields\Text', array($this->validator, $this->config, $this->db, $options))->makePartial();
    }

    /**
     * Tear down function
     */
    public function tearDown()
    {
        m::close();
    }

    public function testFilterQueryWithValue()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');
        $query->shouldReceive('where')->once();
        $this->config->shouldReceive('getDataModel')->twice()->andReturn(m::mock(array('getTable' => 'table')));
        $this->field->shouldReceive('getOption')->times(4)->andReturn(false, 'test');
        $this->field->filterQuery($query);
    }

    public function testFilterQueryWithoutValue()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');
        $query->shouldReceive('where')->never();
        $this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getTable' => 'table')));
        $this->field->shouldReceive('getOption')->twice()->andReturn(false);
        $this->field->filterQuery($query);
    }

}
