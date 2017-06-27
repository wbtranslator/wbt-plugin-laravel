@extends ('layouts/translator-main')

@section('content')
<form type="post">
	<fieldset>
		<button type="submit" name="action" value="export">Export</button>
		<button type="submit" name="action" value="import">Import</button>
	</fieldset>
</form>
@isset ($exported['message'])
	{{ $exported['message'][0] }}
@endisset
@isset ($imported['message'])
	<ul>
	@foreach ($imported['message'] as $k => $v)
		<li>{{ $k }} : {{ $v }}</li>
	@endforeach
	</ul>
@endisset
@endsection