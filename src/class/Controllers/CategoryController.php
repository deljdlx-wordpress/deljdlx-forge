<?php
namespace Deljdlx\WPForge\Controllers;

use Deljdlx\WPForge\Models\Category;

class CategoryController extends BaseController
{
    public function add($categoryName)
    {
        $category = new Category();
        $category->name = $categoryName;
        $category->save();
    }

    public function delete($id)
    {
        $category = Category::find($id);
        $category->delete();
    }

    public function index()
    {

        $categories = Category::all();
        echo $this->theme->view->render(
            'categories',
            [
                'categories' => $categories
            ]
        );
    }
}