@extends ('translator-layout')

@section('content')
	<form type="get">
		<button type="submit" name="action" value="export">Export</button>
		<button type="submit" name="action" value="import">Import</button>
	</form>
	@isset ($exported)
		<p>Export: {{ $exported }} tasks</p>
	@endisset
	@isset ($imported)
		<h4>Imported:</h4>
		<ul>
		@foreach ($imported as $k => $v)
			<li>{{ $k }} : {{ $v }}</li>
		@endforeach
		</ul>
	@endisset
	@isset ($exception)
		<p style="fint-weight: bold; color: red;">ERROR: {{ $exception->getMessage() }}</p>
	@endisset
@endsection