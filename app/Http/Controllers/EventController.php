<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    //
    public function index() {

        // Recebe todos os registros da model (equivalente ao select * from tabela)
        $events = Event::all();

        return view('welcome', ['events' => $events]);
    }

    public function create() {
        return view('events.create');
    }

    // O parâmetro "$request" recebe todos os atributos da view
    public function store(Request $request) {

        // Cria um novo objeto a partir da instância da classe da Model "Event" 
        $event = new Event;

        $event->title = $request->title;
        $event->city = $request->city;
        $event->description = $request->description;
        $event->private = $request->private;

        $event->save();

        return redirect('/')->with('msg','Evento criado com sucesso!');
    }
}
