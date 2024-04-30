<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;

class EventController extends Controller
{
    //
    public function index() {

        $search = request('search');

        if ($search) {
            
            $events = Event::where([
                ['title', 'like', '%'.$search.'%']
            ])->get();

        } else {

            // Recebe todos os registros da model (equivalente ao select * from tabela)
            $events = Event::all();
        }
        
        return view('welcome', ['events' => $events, 'search' => $search]);
    }

    public function create() {
        return view('events.create');
    }

    // O parâmetro "$request" recebe todos os atributos da view
    public function store(Request $request) {

        // Cria um novo objeto a partir da instância da classe da Model "Event" 
        $event = new Event;

        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->description = $request->description;
        $event->private = $request->private;
        $event->items = $request->items;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $extension = $request->image->extension();
            $imageName = md5($request->image->getClientOriginalName() . strtotime('now')) . "." . $extension;

            $request->image->move(public_path('img/events/'), $imageName);
            $event->image = $imageName;
        }

        $user = auth()->user();
        $event->user_id = $user->id;
        
        $event->save();

        return redirect('/')->with('msg','Evento criado com sucesso!');
    }

    public function show($id) {
        
        $event = Event::findOrFail($id);

        $user = auth()->user();

        $hasUserJoined = false;

        if ($user) {
            $userEvents = $user->eventsAsParticipant->toArray();

            foreach($userEvents as $userEvent) {
                if ($userEvent['id'] == $id) {
                    $hasUserJoined = true;
                }
            }
        }

        $eventOwner = User::where('id', '=', $event->user_id)->first()->toArray();

        return view('events.show', ['event' => $event, 'eventOwner' => $eventOwner, 'hasUserJoined' => $hasUserJoined]);

    }

    public function dashboard() {
        
        $user = auth()->user();

        $events = $user->events;

        $eventAsParticipant = $user->eventsAsParticipant;

        return view('events.dashboard', ['events' => $events, 'eventasparticipant' => $eventAsParticipant]);

    }

    public function destroy($id) {

        $event = Event::findOrFail($id)->delete();

        return redirect('/dashboard')->with('msg','Evento excluído com sucesso!');

    }

    public function edit($id) {

        $user = auth()->user();

        $event = Event::findOrFail($id);

        if ($user->id != $event->user->id) {
            return redirect('/dashboard');
        }
        
        return view('events.edit', ['event' => $event]);
    }

    public function update(Request $request) {

        $data = $request->all();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $extension = $request->image->extension();
            $imageName = md5($request->image->getClientOriginalName() . strtotime('now')) . "." . $extension;

            $request->image->move(public_path('img/events/'), $imageName);
            $data['image'] = $imageName;
        }

        $event = Event::findOrFail($request->id)->update($data);

        return redirect('/dashboard')->with('msg','Evento editado com sucesso!');
    }

    public function joinEvent($id) {
        
        $user = auth()->user();

        $user->eventsAsParticipant()->attach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg','Sua presença está confirmada no evento ' . $event->title);
    }

    public function leaveEvent($id) {
        
        $user = auth()->user();

        $user->eventsAsParticipant()->detach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg','Sua presença está removida no evento ' . $event->title);

    }
}