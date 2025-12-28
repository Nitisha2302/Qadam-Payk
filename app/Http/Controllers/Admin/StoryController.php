<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Story;
use App\Models\StoryReport;
use Illuminate\Support\Facades\File;

class StoryController extends Controller
{
    /* =====================================================
       1️⃣ LIST ALL REPORTED STORIES
        ===================================================== */
    public function reportedStories(Request $request)
    {
        $stories = Story::with(['user'])
            ->withCount('reports')
            ->having('reports_count', '>', 0)
            ->when($request->search, function ($q) use ($request) {
                $q->where('city', 'like', "%{$request->search}%")
                ->orWhereHas('user', function ($u) use ($request) {
                    $u->where('name', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('reports_count')
            ->paginate(10);

        return view('admin.story.reported-stories', compact('stories'));
    }

    public function reportedStoryDetail($id)
    {
        $story = Story::with([
                'user',
                'reports.user'
            ])
            ->withCount('reports')
            ->find($id);

        if (!$story) {
            return redirect()
                ->route('dashboard.admin.reported-stories')
                ->with('error', 'Story not found');
        }

        return view('admin.story.reported-story-detail', compact('story'));
    }


    public function deleteStory($id)
    {
        $story = Story::find($id);
        if(!$story) {
            return response()->json(['status' => false, 'message' => 'Story not found'], 404);
        }

        // Delete media file
        if($story->media) {
            $mediaPath = public_path('assets/story_media/' . $story->media);
            if(File::exists($mediaPath)) {
                File::delete($mediaPath);
            }
        }

        // Delete reports
        StoryReport::where('story_id', $id)->delete();

        // Delete story
        $story->delete();

        return response()->json(['status' => true, 'message' => 'Story deleted successfully']);
    }
}
