<?php
namespace DDPro\Admin\DataTable;

use DDPro\Admin\Config\ConfigInterface;
use DDPro\Admin\DataTable\Columns\Column;
use DDPro\Admin\DataTable\Columns\Factory as ColumnFactory;
use DDPro\Admin\Fields\Factory as FieldFactory;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Log;
use Mockery\CountValidator\Exception;

/**
 * Class DataTable
 *
 * This defines the basic operations for a data table based on a Laravel model class,
 * This includes things like holding the configuration instance (from which the model
 * class can be determined), the validation instance, the Field Factory, the relationships,
 * etc.
 *
 * The DataTable manages the table view in the model index page.  Each column in the
 * DataTable is represented by a Column object.
 *
 * ### Example
 *
 * ```php
 * // Example code goes here
 * ```
 *
 * @see DDPro\Admin\DataTable\Columns\Factory
 * @see DDPro\Admin\DataTable\Columns\Column
 * @link https://github.com/ddpro/admin/blob/master/docs/columns.md
 */
class DataTable
{

    /**
     * The config instance
     *
     * @var \DDPro\Admin\Config\ConfigInterface
     */
    protected $config;

    /**
     * The column factory instance
     *
     * @var \DDPro\Admin\DataTable\Columns\Factory
     */
    protected $columnFactory;

    /**
     * The field factory instance
     *
     * @var \DDPro\Admin\Fields\Factory
     */
    protected $fieldFactory;

    /**
     * The column objects
     *
     * @var array
     */
    protected $columns;

    /**
     * The sort options
     *
     * @var array
     */
    protected $sort;

    /**
     * The number of rows per page for this data table
     *
     * @var int
     */
    protected $rowsPerPage = 20;

    /**
     * Create a new action DataTable instance
     *
     * @param \DDPro\Admin\Config\ConfigInterface		$config
     * @param \DDPro\Admin\DataTable\Columns\Factory	$columnFactory
     * @param \DDPro\Admin\Fields\Factory				$fieldFactory
     */
    public function __construct(ConfigInterface $config, ColumnFactory $columnFactory, FieldFactory $fieldFactory)
    {
        // set the config, and then validate it
        $this->config        = $config;
        $this->columnFactory = $columnFactory;
        $this->fieldFactory  = $fieldFactory;
    }

    /**
     * Builds a results array (with results and pagination info)
     *
     * Used for server side DataTable
     *
     * @param \Illuminate\Database\DatabaseManager 	$db
     * @param array									$input
     *
     * @return array
     */
    public function getDataTableRows(DB $db, $input)
    {
        // prepare the query
        // Don't use this syntax, only because it makes it impossible for phpStorm to verify the
        // presence and type of the variables.
        // extract($this->prepareQuery($db, $page, $sort, $filters));
        // This is functionally equivalent.
        /** @var QueryBuilder $query */
        list($query, $querySql, $queryBindings, $countQuery, $sort, $selects) =
            $this->prepareQuery($db, $input);

        // run the count query
        $countResult = $this->performCountQuery($countQuery, $querySql, $queryBindings, 1);

        // now we need to limit and offset the rows in remembrance of our dear lost friend paginate()
        if (! empty($input['length'])) {
            $query->take($input['length']);
        }
        if (! empty($input['start'])) {
            $query->skip($input['start']);
        }

        // parse the results
        $output['recordsTotal']     = $countResult['total'];
        $output['recordsFiltered']  = $countResult['total'];
        $output['data']             = $this->parseResults($query->get());
        if (! empty($input['draw'])) {
            $output['draw']         = (integer) $input['draw'];
        }

        return $output;
    }

    /**
     * Builds a results array (with results and pagination info)
     *
     * @param \Illuminate\Database\DatabaseManager 	$db
     * @param array									$input
     *
     * @return array
     */
    public function prepareQuery(DB $db, $input = [])
    {
        // grab the model instance
        /** @var Model $model */
        $model = $this->config->getDataModel();

        if (! isset($input['columns']) || ! is_array($input['columns'])) {
            Log::warning(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'Input array does not contain a columns array, cannot get column options.', $input);
        } else {
            // Grab the columns array from the input
            // We have provided input columns so we can get the sort structure from that.
            $inputColumns = $input['columns'];
        }

        // update the sort options
        $sort = [];
        if (isset($input['order']) && is_array($input['order']) && isset($inputColumns)) {

            // If this is set then it will have this structure:
            // ['column' => $column_number, 'dir' => 'asc'|'desc']
            // Have to find the column name from the column number
            $inputOrder = $input['order'][0];
            $sort       = [
                'field'     => $inputColumns[$inputOrder['column']]['name'],
                'direction' => $inputOrder['dir'],
            ];
        }
        $this->setSort($sort);
        $sort = $this->getSort();

        // get things going by grouping the set
        $table   = $model->getTable();
        $keyName = $model->getKeyName();

        /** @var EloquentBuilder $query */
        $query   = $model->groupBy($table . '.' . $keyName);

        // get the Illuminate\Database\Query\Builder instance and set up the count query
        $dbQuery    = $query->getQuery();
        $countQuery = $dbQuery->getConnection()->table($table)->groupBy($table . '.' . $keyName);

        // run the supplied query filter for both queries if it was provided
        $this->config->runQueryFilter($dbQuery);
        $this->config->runQueryFilter($countQuery);

        // set up initial array states for the selects
        $selects = [$table . '.*'];

        // set the filters
        if (isset($input['filters'])) {
            foreach ($input['filters'] as $key => &$value) {
                if (isset($value['value']) && is_array($value['value'])) {
                    foreach ($value['value'] as $key1 => $value1) {
                        if (empty($value1)) {
                            unset($value['value'][$key1]);
                        }
                    }
                }
            }
            $this->setFilters($input['filters'], $dbQuery, $countQuery, $selects);
        }

        // set the selects
        $dbQuery->select($selects);

        // determines if the sort should have the table prefixed to it
        $sortOnTable = true;

        // get the columns
        $columns = $this->columnFactory->getColumns();

        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'columns = ', $columns);

        // iterate over the columns to check if we need to join any values or add any extra columns
        /** @var Column $column */
        foreach ($columns as $column) {
            // if this is a related column, we'll need to add some selects
            $column->filterQuery($selects);

            // if this is a related field or
            if (($column->getOption('is_related') || $column->getOption('select')) && $column->getOption('column_name') === $sort['field']) {
                $sortOnTable = false;
            }
        }

        // if the sort is on the model's table, prefix the table name to it
        if ($sortOnTable) {
            $sort['field'] = $table . '.' . $sort['field'];
        }

        // grab the query sql for later
        $querySql = $query->toSql();

        // order the set by the model table's id
        $query->orderBy($sort['field'], $sort['direction']);

        // then retrieve the rows
        $query->getQuery()->select($selects);

        // only select distinct rows
        $query->distinct();

        // load the query bindings
        $queryBindings = $query->getBindings();

        // return compact('query', 'querySql', 'queryBindings', 'countQuery', 'sort', 'selects');
        return [$query, $querySql, $queryBindings, $countQuery, $sort, $selects];
    }

    /**
     * Performs the count query and returns info about the pages
     *
     * @param \Illuminate\Database\Query\Builder	$countQuery
     * @param string								$querySql
     * @param array									$queryBindings
     * @param int									$page
     *
     * @return array
     */
    public function performCountQuery(QueryBuilder $countQuery, $querySql, $queryBindings, $page)
    {
        // grab the model instance
        $model = $this->config->getDataModel();

        // then wrap the inner table and perform the count
        $sql = "SELECT COUNT({$model->getKeyName()}) AS aggregate FROM ({$querySql}) AS agg";

        // then perform the count query
        $results = $countQuery->getConnection()->select($sql, $queryBindings);
        $numRows = is_array($results[0]) ? $results[0]['aggregate'] : $results[0]->aggregate;
        $page    = (int) $page;
        $last    = (int) ceil($numRows / $this->rowsPerPage);

        return [
            // if the current page is greater than the last page, set the current page to the last page
            'page'  => $page > $last ? $last : $page,
            'last'  => $last,
            'total' => $numRows,
        ];
    }

    /**
     * Sets the query filters when getting the rows
     *
     * This takes an array of this structure:
     *
     * ```php
     * [
     *     'field_name'     => $field_name,
     *     'value'          => $value,
     *     'min_value'      => $min_value,
     *     'max_value'      => $max_value,
     * ]
     * ```
     *
     * @param array									$filters
     * @param \Illuminate\Database\Query\Builder	$query
     * @param \Illuminate\Database\Query\Builder	$countQuery
     * @param array									$selects
     */
    public function setFilters($filters, QueryBuilder &$query, QueryBuilder &$countQuery, &$selects)
    {
        // then we set the filters
        if ($filters && is_array($filters)) {
            foreach ($filters as $filter) {
                // get the field object
                $fieldObject = $this->fieldFactory->findFilter($filter['field_name']);

                // set the filter on the object
                $fieldObject->setFilter($filter);

                // filter the query objects, only pass in the selects the first time so they aren't added twice
                $fieldObject->filterQuery($query, $selects);
                $fieldObject->filterQuery($countQuery);
            }
        }
    }

    /**
     * Parses the results of a getRows query and converts it into a manageable array with the proper rendering
     *
     * @param 	Collection|array	$rows
     *
     * @return	array
     */
    public function parseResults($rows)
    {
        $results = [];

        // convert the resulting set into arrays
        foreach ($rows as $item) {
            // iterate over the included and related columns
            $arr = [];

            $this->parseOnTableColumns($item, $arr);

            // then grab the computed, unsortable columns
            $this->parseComputedColumns($item, $arr);

            $results[] = $arr;
        }

        return $results;
    }

    /**
     * Goes through all related columns and sets the proper values for this row
     *
     * @param \Illuminate\Database\Eloquent\Model	$item
     * @param array									$outputRow
     *
     * @return void
     */
    public function parseOnTableColumns($item, array &$outputRow)
    {
        $columns         = $this->columnFactory->getColumns();
        $includedColumns = $this->columnFactory->getIncludedColumns($this->fieldFactory->getEditFields());
        $relatedColumns  = $this->columnFactory->getRelatedColumns();

        $disk    = config('filesystems.default');
        $storage = \Storage::disk($disk);

        // loop over all columns
        foreach (array_merge($columns, $includedColumns, $relatedColumns) as $field => $col) {
            // if this column is in our objects array, render the output with the given value
            if (isset($columns[$field])) {
                $attributeValue = $item->getAttribute($field);
                if ($attributeValue) {
                    if ($columns[$field]->getOption('type') == 'image') {
                        $mime_type      = $storage->mimeType($attributeValue);
                        $row_image      = "data:$mime_type;base64," . base64_encode($storage->get($attributeValue));
                        $attributeValue = '<img src="' . $row_image . '" style="max-width: 250px; max-height: 250px" />';
                    }
                }

                $outputRow[] = $columns[$field]->renderOutput($attributeValue, $item);
            }
        }
    }

    /**
     * Goes through all computed columns and sets the proper values for this row
     *
     * @param \Illuminate\Database\Eloquent\Model	$item
     * @param array									$outputRow
     *
     * @return void
     */
    public function parseComputedColumns($item, array &$outputRow)
    {
        Log::warning(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'parse computed columns called, this has not been checked');

        $columns         = $this->columnFactory->getColumns();
        $computedColumns = $this->columnFactory->getComputedColumns();

        // loop over the computed columns
        foreach ($computedColumns as $name => $column) {
            $outputRow[] = $columns[$name]->renderOutput($item->{$name}, $item);
        }
    }

    /**
     * Sets up the sort options
     *
     * Takes in an array like this:
     *
     * ```php
     * [
     *     'field'     => $fieldname,
     *     'direction' => 'asc'|'desc',
     * ]
     * ```
     *
     * @param array		$sort
     */
    public function setSort($sort = null)
    {
        $sort = $sort && is_array($sort) ? $sort : $this->config->getOption('sort');

        // set the sort values
        $this->sort = [
            'field'     => isset($sort['field']) ? $sort['field'] : $this->config->getDataModel()->getKeyName(),
            'direction' => isset($sort['direction']) ? $sort['direction'] : 'desc',
        ];

        // if the sort direction isn't valid, set it to 'desc'
        if (! in_array($this->sort['direction'], ['asc', 'desc'])) {
            $this->sort['direction'] = 'desc';
        }
    }

    /**
     * Gets the sort options
     *
     * @return array
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set the number of rows per page for this data table
     *
     * @param \Illuminate\Session\Store	$session
     * @param int						$globalPerPage
     * @param int						$override	// if provided, this will set the session's rows per page value
     */
    public function setRowsPerPage(\Illuminate\Session\Store $session, $globalPerPage, $override = null)
    {
        if ($override) {
            $perPage = (int) $override;
            $session->put('administrator_' . $this->config->getOption('name') . '_rows_per_page', $perPage);
        }

        $perPage = $session->get('administrator_' . $this->config->getOption('name') . '_rows_per_page');

        if (! $perPage) {
            $perPage = (int) $globalPerPage;
        }

        $this->rowsPerPage = $perPage;
    }

    /**
     * Gets the rows per page
     *
     * @return int
     */
    public function getRowsPerPage()
    {
        return $this->rowsPerPage;
    }
}
