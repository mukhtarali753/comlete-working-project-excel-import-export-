@extends('layouts.theme')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Create Theme Block</h4>
    </div>
    <div class="card-body">
        
        <form action="{{ route('theme_blocks.store', $themeId) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Type something..."></textarea>
            </div>

            

            {{-- <button type="submit" class="btn btn-success">Create Theme Block</button> --}}

{{--            
            <div class="mb-3" style="margin-top: 2rem">
                
                <textarea class="form-control" rows="5" id="textarea"></textarea>
            </div> --}}

            <div class="mb-3">
              
                <textarea name="body" class="form-control" id="editor" rows="5" placeholder="Type something..."></textarea>

            </div>
            

            {{-- <button type="submit" class="btn btn-success">Submit</button> --}}

             <button type="submit" class="btn btn-success">Create Theme Block</button> 

        </form>
    </div>
</div>


<script>
    Jodit.make('#editor', {
        uploader: {
            url: '/upload-image', // Laravel route
            insertImageAsBase64URI: false
        },
        
        buttons: [
            'bold', 'italic', 'underline', '|', 'ul', 'ol', 'image', 'video', '|', 'undo', 'redo', 'source'
        ],
        iframe: false,
        safeMode: false,
        allowTags: '*',
        cleanHTML: false
    });
</script>







  


        
   

  



@endsection
