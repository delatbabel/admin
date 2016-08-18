<?php
namespace DDPro\Admin\Tests\DataTable\Columns\Relationships;

use Mockery as m;

class BelongsToManyTest extends \PHPUnit_Framework_TestCase {

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
     * The Column mock
     *
     * @var Mockery
     */
    protected $column;

    /**
     * Set up function
     */
    public function setUp()
    {
        $this->validator = m::mock('DDPro\Admin\Validator');
        $this->config = m::mock('DDPro\Admin\Config\Model\Config');
        $this->db = m::mock('Illuminate\Database\DatabaseManager');

        $options = array('column_name' => 'test', 'relationship' => 'method', 'select' => 'foo');
        $this->column = m::mock('DDPro\Admin\DataTable\Columns\Relationships\BelongsToMany',
                                            array($this->validator, $this->config, $this->db, $options))->makePartial();
    }

    /**
     * Tear down function
     */
    public function tearDown()
    {
        m::close();
    }

    public function testGetIncludedColumn()
    {
        $model = m::mock(array('getTable' => 'table', 'method' => m::mock(array('getRelated' => m::mock(array('getKeyName' => 'fk'))))));
        $this->config->shouldReceive('getDataModel')->once()->andReturn($model);
        $this->column->shouldReceive('getOption')->once()->andReturn('method');
        $this->assertEquals($this->column->getIncludedColumn(), array('fk' => 'table.fk'));
    }

}
