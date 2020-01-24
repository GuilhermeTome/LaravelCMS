<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Visitor;
use App\Page;
use App\User;

class HomeController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        $counts = [
            'visits' => 0,
            'online' => 0,
            'page' => 0,
            'user' => 0
        ];

        $dateLimit = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $onlineList = Visitor::select('ip')->where('date_access', '>=', $dateLimit)->groupBy('ip')->get();
        $counts['online'] = count($onlineList);

        $counts['page'] = Page::count();

        $counts['user'] = User::count();

        //PagePie
        $dias = (!isset($request->only(['dias'])['dias'])) ? 2 : $request->only(['dias'])['dias'];
        $pagePie = [];
        
        if ($dias == 1) {
            $dateTime = date('Y-m-d H:i:s', strtotime('-1 day'));
            $visitsText = 'Acesso de Hoje';
        } else if ($dias == 2) {
            $dateTime = date('Y-m-d H:i:s', strtotime('-30 day'));
            $visitsText = 'Acesso nos últimos 30 dias';
        } else if ($dias == 3) {
            $dateTime = date('Y-m-d H:i:s', strtotime('-60 day'));
            $visitsText = 'Acesso nos últimos 60 dias';
        }

        $counts['visits'] = count(Visitor::select()->where('date_access', '>=', $dateTime)->get());
    
        $visitsAll = Visitor::selectRaw('page, count(page) as c')->where('date_access', '>=', $dateTime)->groupBy('page')->get();
        foreach ($visitsAll as $visit) {
            $pagePie[$visit['page']] = intval($visit['c']);
        }

        $pageLabels = json_encode(array_keys($pagePie));
        $pageValues = json_encode(array_values($pagePie));

        return view('admin.home', [
            'visitsCount' => $counts['visits'],
            'onlineCount' => $counts['online'],
            'pageCount' => $counts['page'],
            'userCount' => $counts['user'],
            'pageLabels' => $pageLabels,
            'pageValues' => $pageValues,
            'dias' => $dias,
            'visitsText' => $visitsText
        ]);
    }
    
}
