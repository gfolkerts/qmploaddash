
function toggle(myElementID,myStatus){
  var oDiv = document.getElementById(myElementID);
  if(oDiv != null) {
    if(myStatus=='AUTO') {
      oDiv.style.display = oDiv.style.display=='none'?'block':'none';
    } 
    if(myStatus=='ON') {
      oDiv.style.display = 'block';
    } 
    if(myStatus=='OFF') {
      oDiv.style.display = 'none';
    } 
  }	
}

function ShowParticipant(myParticipant) {
	toggle(myParticipant+'_add','OFF');
	toggle(myParticipant+'_up','ON');
	toggle(myParticipant+'_res','ON');
}

function HideParticipant(myParticipant) {
	toggle(myParticipant+'_add','ON');
	toggle(myParticipant+'_up','OFF');
	toggle(myParticipant+'_res','OFF');
}



