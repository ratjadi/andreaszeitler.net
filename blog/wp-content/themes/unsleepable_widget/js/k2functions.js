function ShowUtils() {
	new Effect.Phase('authorinfo');
	$("showinfo").style.display = "none";
	$("hideinfo").style.display = "";
}

function HideUtils() {
	new Effect.Phase('authorinfo');
	$("showinfo").style.display = "";
	$("hideinfo").style.display = "none";
}