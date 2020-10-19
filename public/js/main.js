const table = document.getElementById("table");
if(table) {
    table.addEventListener("click", (e) => {
        if(e.target.className === 'btn btn-danger delete-article'){
            if(confirm('are u sure?')){
                const id = e.target.getAttribute('data-id');
                fetch(`/article/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    })
}