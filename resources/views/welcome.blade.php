<h1>Usuarios</h1>
<br>
@foreach ($users as $user )
    <p>{{$user->name}}</p>
    <br>
    <p>{{$user->email}}</p>
    <br>
@endforeach