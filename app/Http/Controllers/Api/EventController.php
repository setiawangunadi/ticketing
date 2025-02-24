<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventCategory;
use App\Models\Event;

class EventController extends Controller
{
    //index
    public function index(Request $request)
    {
        //event by category_id
        // $events = Event::where('event_category_id', $request->category_id)->get();
        $categoryId = $request->input('category_id');
        //if category_id all
        if ($request->category_id == 'all') {
            $events = Event::all();
        } else {
            $events = Event::where('event_category_id', $categoryId)->get();
        }
        //response all event
        // load event_category and vendor
        $events->load('eventCategory', 'vendor');
        return response()->json([
            'status' => 'success',
            'message' => 'Event fetched successfully',
            'data' => $events,
        ]);
    }

    //get all event categories
    public function eventCategories()
    {
        $eventCategories = EventCategory::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Event categories fetched successfully',
            'data' => $eventCategories,
        ]);
    }

    //detail event and sku by event_id
    public function detail(Request $request)
    {
        //event by event_id
        $event = Event::find($request->event_id);
        //response event
        // load event_category and vendor
        $event->load('eventCategory', 'vendor');
        $sku = $event->sku;
        $event['sku'] = $sku;
        return response()->json([
            'status' => 'success',
            'message' => 'Event fetched successfully',
            'data' => $event,
        ]);
    }
}
