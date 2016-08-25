<?php
namespace DDPro\Admin\Tests\Fields\Relationships;

require_once __DIR__ . '/../../LogStub.php';

use Mockery as m;

class BelongsToEloquentStub {
    public $rel;
    public function __unset($rel) {unset($this->{$rel});}
}

class BelongsToTest extends \PHPUnit_Framework_TestCase {

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

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Stub out the log facade
        if (! class_exists('Log')) {
            class_alias('LogStub', 'Log');
        }
    }

    /**
     * Set up function
     */
    public function setUp()
    {
        $this->validator = m::mock('DDPro\Admin\Validator');
        $this->config = m::mock('DDPro\Admin\Config\Model\Config');
        $this->db = m::mock('Illuminate\Database\DatabaseManager');
        $options = array('field_name' => 'field', 'type' => 'belongs_to');
        $this->field = m::mock('DDPro\Admin\Fields\Relationships\BelongsTo',
                                    array($this->validator, $this->config, $this->db, $options))->makePartial();
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
        $relatedModel = m::mock(array('getTable' => 'table', 'getKeyName' => 'id'));
        $relationship = m::mock(array('getRelated' => $relatedModel, 'getForeignKey' => 'some_id'));
        $model = m::mock(array('getTable' => 'table', 'field' => $relationship));
        $this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
        $this->validator->shouldReceive('arrayGet')->times(6);
        $this->field->shouldReceive('setUpConstraints')->once()
                    ->shouldReceive('loadRelationshipOptions')->once();
        $this->field->build();
    }

    public function testFillModelWithInput()
    {
        $model = new BelongsToEloquentStub;
        $model->rel = '3';
        $this->field->shouldReceive('getOption')->twice()->andReturn('rel_id', 'rel');
        $this->field->fillModel($model, '3');
        $this->assertEquals($model->rel_id, '3');
        $this->assertTrue(!isset($model->rel));
    }

    public function testFillModelWithoutInput()
    {
        $model = new BelongsToEloquentStub;
        $model->rel = '3';
        $this->field->shouldReceive('getOption')->twice()->andReturn('rel_id', 'rel');
        $this->field->fillModel($model, 'false');
        $this->assertEquals($model->rel_id, null);
        $this->assertTrue(!isset($model->rel));
    }

    public function testFilterQueryWithValue()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');
        $query->shouldReceive('where')->once();
        $this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getTable' => 'table')));
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
