<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Upload orders to apply discounts</title>
    </head>

    <body>

    <div class="container">
        <h2>Simple file upload</h2>
        <form method="POST" enctype="multipart/form-data" action="{{ route('discount.filter') }}">
            <div>
                <p>Select JSON files to upload</p>
            </div>
            <input type="file" name="json_files[]" multiple="multiple" />
            {!! csrf_field() !!}
            <div>
                <input type="submit" value="Upload"/>
            </div>
        </form>
    </div>

    @if(count($errors))
    <ul class="alert alert-danger">
        @foreach($errors->all() as $error)
           <li>{{$error}}</li>
         @endforeach
    </ul>
    @endif

    </body>

</html>