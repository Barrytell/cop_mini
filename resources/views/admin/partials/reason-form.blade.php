@props(['action', 'label' => 'Confirm', 'method' => 'POST', 'confirm' => null])
<form method="POST" action="{{ $action }}" class="mt-3 space-y-2" @if($confirm) onsubmit="return confirm(@js($confirm))" @endif>
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif
    <label class="label" for="reason-{{ md5($action) }}">Reason</label>
    <input class="field" id="reason-{{ md5($action) }}" name="reason" required maxlength="500" placeholder="Mandatory note">
    <button type="submit" class="btn-primary">{{ $label }}</button>
</form>