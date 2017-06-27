@extends ('layouts/translator-main')

@section('content')
	<form type="get">
		<button type="submit" name="action" value="export">Export</button>
		<button type="submit" name="action" value="import">Import</button>
	</form>
@isset ($exported['message'])
	<p>{{ $exported['message'][0] }}</p>
@endisset
@isset ($imported['message'])
	<ul>
	@foreach ($imported['message'] as $k => $v)
		<li>{{ $k }} : {{ $v }}</li>
	@endforeach
	</ul>
@endisset
@endsection