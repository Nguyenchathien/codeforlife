<?php

namespace NCH\Codeforlife\Http\Controllers;

use Illuminate\Http\Request;
use NCH\Codeforlife\Models\DataType;
use NCH\Codeforlife\Codeforlife;

class CodeforlifeRoleController extends CodeforlifeBreadController
{
    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        Codeforlife::can('edit_roles');

        $slug = $this->getSlug($request);

        $dataType = DataType::where('slug', '=', $slug)->first();

        $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        $data->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route("codeforlife.{$dataType->slug}.index")
            ->with([
                'message'    => "Successfully Updated {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]);
    }

    // POST BRE(A)D
    public function store(Request $request)
    {
        Codeforlife::can('add_roles');

        $slug = $this->getSlug($request);

        $dataType = DataType::where('slug', '=', $slug)->first();

        if (function_exists('codeforlife_add_post')) {
            codeforlife_add_post($request);
        }

        $data = new $dataType->model_name();
        $this->insertUpdateData($request, $slug, $dataType->addRows, $data);

        $data->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route("codeforlife.{$dataType->slug}.index")
            ->with([
                'message'    => "Successfully Added New {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]);
    }
}
