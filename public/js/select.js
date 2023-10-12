window.addEventListener('load', function() {

    const selectChanges = (element)=> {

        let selectAqui = document.getElementById('sel-action');

        if (element.value === 'PAS'){
            selectAqui.classList.remove("d-none");
        }else{
            selectAqui.classList.add("d-none");
        }
    }
    let selectAction = document.getElementById('sel-action');
    selectAction.addEventListener('change', selectChanges(selectAction))
    
})