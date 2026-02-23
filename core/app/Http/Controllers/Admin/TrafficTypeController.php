<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrafficType;
use Illuminate\Http\Request;

class TrafficTypeController extends Controller {
    public function index(Request $request) {
        $pageTitle = 'Traffic Types';
        $traffics  = TrafficType::searchable(['name'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.traffic_type.index', compact('pageTitle', 'traffics'));
    }

    public function store(Request $request, $id = 0) {
        $request->validate([
            'name' => 'required|max:40|unique:traffic_types,name,' . $id,
        ]);

        if ($id) {
            $traffic      = TrafficType::findOrFail($id);
            $notification = 'Traffic type updated successfully';
        } else {
            $traffic      = new TrafficType();
            $notification = 'Traffic type added successfully.';
        }
        $traffic->name = $request->name;
        $traffic->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id) {
        return TrafficType::changeStatus($id);
    }
}
