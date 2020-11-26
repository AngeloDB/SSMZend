<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id: SourceInterface.php 1578 2011-01-25 18:39:50Z bento.vilas.boas@gmail.com $
 * @link      http://zfdatagrid.com
 */

interface Bvb_Grid_Source_SourceInterface
{


    /**
     * Should return true|false if this source support
     * crud operations
     *
     * @return bool
     */
    public function hasCrud ();


    /**
     * Gets a unique record as a associative array
     *
     * @param string $table
     * @param array $condition
     *
     * @return array
     */
    public function getRecord ($table, array $condition);


    /**
     * builds a key=>value array
     *
     * they must have two options
     * title and field
     * field is used to perform queries.
     * Must have table name or table alias as a prefix
     * ex: user.id | country.population
     *
     * The key for this array is the output field
     * If raw sql is something like
     *
     * select name as alias, country from users
     *
     * the return array must be like this:
     *
     * array('alias'=>array('title'=>'alias','field'=>'users.name'));
     *
     * its not bad idea to apply this to fields titles
     * $title = ucwords(str_replace('_',' ',$title));
     *
     * @return string
     */
    public function buildFields ();


    /**
     * Should return the database server name or source name
     *
     * Ex: mysql, pgsql, array, xml
     *
     * @return string
     */
    public function getSourceName ();


    /**
     * Runs the query and returns the result as a associative array
     *
     * @return array
     */
    public function execute ();


    /**
     * Get a record detail based the current query
     *
     * @param array $where
     *
     * @return array
     */
    public function fetchDetail (array $where);


    /**
     * Return the total of records
     *
     * @return int
     */
    public function getTotalRecords ();


    /**
     * Ex: array('c'=>array('tableName'=>'Country'));
     * where c is the table alias. If the table as no alias,
     * c should be the table name
     *
     * @return array
     */
    public function getTableList ();


    /**
     * Return possible filters values based on field definition
     * This is mostly used for enum fields where the possible
     * values are extracted
     *
     * Ex: enum('Yes','No','Empty');
     *
     * should return
     *
     * array('Yes'=>'Yes','No'=>'No','Empty'=>'Empty');
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getFilterValuesBasedOnFieldDefinition ($field);


    /**
     * Return field type
     * char, varchar, int
     *
     * Note: If the field is enum or set,
     * the value returned must be set or enum,
     * and not the full definition
     *
     * @param string $field
     *
     * @return string
     */

    public function getFieldType ($field);


    /**
     * Returns the "main" table
     * the one after select * FROM {MAIN_TABLE}
     *
     * @return string
     */
    public function getMainTable ();


    /**
     *
     * Build the order part from the query.
     *
     * The first arg is the field to be ordered and the $order
     * arg is the correspondent order (ASC|DESC)
     *
     * If the $reset is set to true, all previous order should be removed
     *
     * @param string $field
     * @param string $order
     * @param bool $reset
     *
     * @return void
     */
    public function buildQueryOrder ($field, $order, $reset = false);


    /**
     * Build the query limit clause
     *
     * @param $start
     * @param $offset
     *
     * @return void
     */
    public function buildQueryLimit ($start, $offset);


    /**
     * Returns the select object
     *
     * @return mixed
     */
    public function getSelectObject ();


    /**
     * returns the selected order
     * that was defined by the user in the query entered
     * and not the one generated by the system
     *
     *If empty a empty array must be returned.
     *
     *Else the array must be like this:
     *
     *Array
     * (
     * [0] => field
     * [1] => ORDER (ASC|DESC)
     * )
     *
     *
     * @return array
     */
    public function getSelectOrder ();


    /**
     * Should perform a query based on the provided by the user
     * select the two fields and return an array $field=>$value
     * as result
     *
     * ex: SELECT $field, $value FROM *
     * array('1'=>'Something','2'=>'Number','3'=>'history')....;
     *
     * @param string $field
     * @param string $value
     *
     * @return array
     */
    public function getDistinctValuesForFilters ($field, $fieldValue, $order = 'name ASC');


    /**
     *
     *Perform a sqlexp
     *
     *$value =  array ('functions' => array ('AVG'), 'value' => 'Population' );
     *
     *Should be converted to
     *SELECT AVG(Population) FROM *
     *
     *$value =  array ('functions' => array ('SUM','AVG'), 'value' => 'Population' );
     *
     *Should be converted to
     *SELECT SUM(AVG(Population)) FROM *
     *
     * @param array $value
     * @param array $where
     *
     * @return array
     */
    public function getSqlExp (array $value, $where = array());


    /**
     * Adds a fulltext search instead of a addcondition method
     *
     *$field has an index search
     *$field['search'] = array('extra'=>'boolean|queryExpansion','indexes'=>'string|array');
     *
     *if no indexes provided, use the field name
     *
     *boolean =>  IN BOOLEAN MODE
     *queryExpansion =>  WITH QUERY EXPANSION
     *
     * @param string $filter
     * @param string $field
     *
     * @return mixed
     */
    public function addFullTextSearch ($filter, $field);


    /**
     * Adds a new condition to the current query
     * $filter is the value to be filtered
     * $op is the operand to be used: =,>=, like, llike,REGEX,
     * $completeField. use the index $completField['field'] to
     * specify the field, to avoid ambiguous
     *
     * @param string $filter
     * @param string $op
     * @param array $completeField
     */
    public function addCondition ($filter, $op, $completeField);


    /**
     *Insert an array of key=>values in the specified table
     * @param string $table
     * @param array $post
     */
    public function insert ($table, array $post);


    /**
     *Update values in a table using the $condition clause
     *
     *The condition clause is a $field=>$value array
     *that should be escaped by YOU (if your class doesn't do that for you)
     * and using the AND operand
     *
     *Ex: array('user_id'=>'1','id_site'=>'12');
     *
     *Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $post
     * @param array $condition
     */
    public function update ($table, array $post, array $condition);


    /**
     * Delete a record from a table
     *
     * The condition clause is a $field=>$value array
     * that should be escaped by YOU (if your class doesn't do that for you)
     * and using the AND operand
     *
     * Ex: array('user_id'=>'1','id_site'=>'12');
     * Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $condition
     */
    public function delete ($table, array $condition);


    /**
     * Removes any order in que query
     */
    public function resetOrder ();


    /**
     * Removes any offset in que query
     */
    public function resetLimit ();


    /**
     * Cache handler.
     *
     * @param Zend_Cache
     */
    public function setCache ($cache);


    /**
     * Build the form based on a Model or query
     *
     * @param array $inputsType
     */
    public function buildForm ($inputsType = array());


    /**
     * Returns tables primary keys separeted by commas ","
     * This is necessary for mass actions
     *
     * @param string $table
     * @param array $fields
     */
    public function getMassActionsIds ($table, $fields);


    /**
     *
     * Quotes a string
     *
     * @param string $value Field Value
     */
    public function quoteValue($value);


    /**
     * Fetch pairs from a table
     *
     * @param string $table      Table Name
     * @param string $field      Field Name
     * @param string $fieldValue Field Value
     * @param string $order      Query Order
     *
     */
    public function getValuesForFiltersFromTable ($table, $field, $fieldValue, $order = 'name ASC');
}