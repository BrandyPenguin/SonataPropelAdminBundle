<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\PropelAdminBundle\Tests\Builder;

use Sonata\PropelAdminBundle\Builder\ListBuilder;
use Sonata\PropelAdminBundle\Admin\FieldDescription;

/**
 * ListBuilder tests
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class ListBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $admin;
    protected $typeGuesser;
    protected $list;

    public function setUp()
    {
        // configure the admin
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        // configure the typeGuesser
        $this->typeGuesser = $this->getMock('Sonata\AdminBundle\Guesser\TypeGuesserInterface');

        // configure the fields list
        $this->list = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionCollection');
    }

    /**
     * @group           templates
     * @dataProvider    addFieldFixesTemplateProvider
     */
    public function testAddFieldFixesTemplate($templatesMap, $field, $type, $expectedTemplate)
    {
        $builder = new ListBuilder($this->typeGuesser, $templatesMap);
        $builder->addField($this->list, $type, $field, $this->admin);

        $this->assertSame($expectedTemplate, $field->getTemplate());
    }

    public function addFieldFixesTemplateProvider()
    {
        $templatesMap = array(
            'text'      => 'textTemplate.html.twig',
            'integer'   => 'integerTemplate.html.twig',
        );

        // configure the fields descriptions
        $field = new FieldDescription();
        $field->setTemplate('customTextTemplate.html.twig');

        return array(
            array($templatesMap, new FieldDescription(), 'text',    'textTemplate.html.twig'),
            array($templatesMap, new FieldDescription(), 'integer', 'integerTemplate.html.twig'),
            array($templatesMap, $field,                 'text',    'customTextTemplate.html.twig'),
            array($templatesMap, new FieldDescription(), 'boolean',  null),
        );
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testAddFieldFixesFieldDescription($field, $givenOptions, $expectedOptions)
    {
        $field->setOptions($givenOptions);

        $builder = new ListBuilder($this->typeGuesser);
        $builder->addField($this->list, 'text', $field, $this->admin);

        foreach ($expectedOptions as $option => $value) {
            $this->assertSame($value, $field->getOption($option), 'Testing option ' . $option);
        }
    }

    public function optionsProvider()
    {
        $field = new FieldDescription();
        $field->setName('my_field');

        return array(
            /****************************
             * Sorting related options
             ***************************/
            // the field isn't sortable: nothing is touched
            array(
                $field,
                array('sortable' => false),
                array('sortable' => false, 'sort_field_mapping' => null, 'sort_parent_association_mappings' => null)
            ),
            // sortable field, sort_field_mapping and sort_parent_association_mappings are updated
            array(
                $field,
                array('sortable' => true),
                array('sortable' => true, 'sort_field_mapping' => null, 'sort_parent_association_mappings' => array())
            ),
            array(
                $field,
                array('sortable' => true, 'sort_field_mapping' => 'sort_field_mapping value', 'sort_parent_association_mappings' => 'sort_parent_association_mappings value'),
                array('sortable' => true, 'sort_field_mapping' => 'sort_field_mapping value', 'sort_parent_association_mappings' => 'sort_parent_association_mappings value')
            ),
            // sort order is always updated
            array(
                $field,
                array(),
                array('_sort_order' => 'ASC') // default value
            ),
            array(
                $field,
                array('_sort_order' => 'DESC'),
                array('_sort_order' => 'DESC') // given value
            ),

            /****************************
             * Code and label related options
             ***************************/
            // the default code is the field's name
            array(
                $field,
                array(),
                array('code' => 'my_field')
            ),
            // the default label is the field's name
            array(
                $field,
                array(),
                array('label' => 'my_field')
            ),
            // code and label are updated if given
            array(
                $field,
                array('code' => 'super code', 'label' => 'super label'),
                array('code' => 'super code', 'label' => 'super label')
            ),
        );
    }
}
