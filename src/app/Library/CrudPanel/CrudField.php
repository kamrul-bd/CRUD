<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

/**
 * Adds fluent syntax to Backpack CRUD Fields.
 *
 * In addition to the existing:
 * - CRUD::addField(['name' => 'price', 'type' => 'number']);
 *
 * Developers can also do:
 * - CRUD::field('price')->type('number');
 *
 * And if the developer uses CrudField as Field in their CrudController:
 * - Field::name('price')->type('number');
 */
class CrudField
{
    protected $crud;
    protected $attributes;

    public function __construct(CrudPanel $crud, $name)
    {
        $this->crud = $crud;

        $field = $this->crud->firstFieldWhere('name', $name);

        // if field exists
        if ((bool) $field) {
            // use all existing attributes
            $this->setAllAttributeValues($field);
        } else {
            // it means we're creating the field now,
            // so at the very least set the name attribute
            $this->setAttributeValue('name', $name);
        }

        return $this->save();
    }

    /**
     * Create a CrudField object with the parameter as its name.
     *
     * @param  string $name Name of the column in the db, or model attribute.
     * @return CrudPanel
     */
    public static function name($name)
    {
        return new static(app()->make('crud'), $name);
    }

    /**
     * Remove the current field from the current operation.
     * 
     * @return void
     */
    public function remove()
    {
        $this->crud->removeField($this->attributes['name']);
    }

    /**
     * Remove an attribute from the current field definition array.
     * 
     * @param  string $attribute Name of the attribute being removed.
     * @return CrudField
     */
    public function forget($attribute)
    {
        $this->crud->removeFieldAttribute($this->attributes['name'], $attribute);

        return $this;
    }

    // ---------------
    // PRIVATE METHODS
    // ---------------

    /**
     * Set the value for a certain attribute on the CrudField object.
     *
     * @param string $attribute Name of the attribute.
     * @param string $value     Value of that attribute.
     */
    private function setAttributeValue($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Replace all field attributes on the CrudField object
     * with the given array of attribute-value pairs.
     *
     * @param array $array Array of attributes and their values.
     */
    private function setAllAttributeValues($array)
    {
        $this->attributes = $array;
    }

    /**
     * Update the global CrudPanel object with the current field attributes.
     *
     * @return CrudField
     */
    private function save()
    {
        $key = $this->attributes['name'];

        if ($this->crud->hasFieldWhere('name', $key)) {
            $this->crud->modifyField($key, $this->attributes);
        } else {
            $this->crud->addField($this->attributes);
        }

        return $this;
    }

    /**
     * If a developer calls a method that doesn't exist, assume they want:
     * - the CrudField object to have an attribute with that value;
     * - that field be updated inside the global CrudPanel object;.
     *
     * Eg: type('number') will set the "type" attribute to "number"
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return CrudField
     */
    private function __call($method, $parameters)
    {
        $this->setAttributeValue($method, $parameters[0]);

        return $this->save();
    }
}