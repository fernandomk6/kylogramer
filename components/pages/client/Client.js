class Client {

  constructor() {
    this.loadCards();
    this.form();
    this.search();
  };

  sections = {
    pageTitle: document.querySelector("#client__page-title__section"),
    search: document.querySelector("#client__search__section"),
    content: document.querySelector("#client__content__section"),
    insert: document.querySelector("#client__insert__section"),
    dialogMessage: document.querySelector("#client__dialog-message__section")
  };

  buildCard(id, name, phone) {
    let card = `<div class="client__content__card">
                  <div class="client__content__card__id-box">
                    <span class="client__content__card__id-box__title">Id</span>
                    <p class="client__content__card__id-box__content">${id}</p>
                  </div>
                  <div class="client__content__card__name-box">
                    <span class="client__content__card__name-box__title">Nome</span>
                    <p class="client__content__card__name-box__content">${name}</p>
                  </div>
                  <div class="client__content__card__phone-box">
                    <span class="client__content__card__phone-box__title">Telefone</span>
                    <p class="client__content__card__phone-box__content">${phone}</p>
                  </div>
                  <div class="client__content__card__actions-box">

                    <form data-id="client__content__card__actions-box__update-form">
                      <input type="hidden" name="type" value="update">
                      <input type="hidden" name="id" value="${id}">
                      <button class="client__content__card__actions-box__action" type="submit">Editar</button>
                    </form>

                    <form data-id="client__content__card__actions-box__delete-form">
                      <input type="hidden" name="type" value="delete">
                      <input type="hidden" name="id" value="${id}">
                      <button class="client__content__card__actions-box__action" type="submit">Excluir</button>
                    </form>

                  </div>
                </div>`;
    return card;
  };

  form() {
    let form = document.querySelector("#client__insert__form__form");
    let btnOpenForm = document.querySelector("#client__page-title__add__btn");
    let btnCancelForm = document.querySelector("#client__insert__form__submit-box__cancel__btn");

    btnOpenForm.onclick = () => {
      this.showForm();
    };

    btnCancelForm.onclick = () => {
      this.clearFormData();
      this.showCards();
    };

    form.onsubmit = (e) => {
      e.preventDefault();

      if (this.validation()) {
        let formData = new FormData(form);
        formData.append("type", "insert");
        this.clearFormData();

        (async () => {
          await fetch('./server/client.php', {
            method: "POST",
            header: {
              ContentType: "application/json"
            },
            body: formData
          });

          this.loadCards();

        })();

      }
    }
  };

  showForm() {
    this.sections.pageTitle.classList.add("--hide");
    this.sections.search.classList.add("--hide");
    this.sections.content.classList.add("--hide");
    this.sections.dialogMessage.classList.add("--hide");
    this.sections.insert.classList.remove("--hide");
  };

  showCards() {
    this.sections.pageTitle.classList.remove("--hide");
    this.sections.search.classList.remove("--hide");
    this.sections.content.classList.remove("--hide");
    this.sections.insert.classList.add("--hide");
    this.sections.dialogMessage.classList.add("--hide");
  };

  showDialogMessage(action, message) {

    let btnYes = document.querySelector("#client__dialog-message__action-box__action__yes");
    let btnNo = document.querySelector("#client__dialog-message__action-box__action__no");
    let textBox = document.querySelector("#client__dialog-message__content__text-box");
    textBox.innerHTML = "";
    let span = document.createElement("span");
    span.classList.add("client__dialog-message__content__message");
    span.innerHTML = message;
    textBox.appendChild(span);

    btnNo.onclick = () => {
      this.loadCards();
    };

    btnYes.onclick = () => {
      action();
    };

    this.sections.pageTitle.classList.add("--hide");
    this.sections.search.classList.add("--hide");
    this.sections.content.classList.add("--hide");
    this.sections.insert.classList.add("--hide");
    this.sections.dialogMessage.classList.remove("--hide");
  };

  clearFormData() {
    let form = document.querySelector("#client__insert__form__form");
    form.id.value = "0";
    form.name.value = "";
    form.phone.value = "";
    document.querySelector("#client__insert__form__error-box__section").innerHTML = "";
  };

  validation() {
    let form = document.querySelector("#client__insert__form__form");
    let name = form.name.value;
    let phone = form.phone.value;
    let errorsSection = document.querySelector("#client__insert__form__error-box__section");

    errorsSection.innerHTML = "";
    let errors = [];

    if (!name) {
      errors.push("Nome em branco");
    }

    if (!phone) {
      errors.push("Telefone em branco");
    }

    if (errors.length !== 0) {
      for (const error of errors) {

        let span = document.createElement("span");
        span.classList.add("client__insert__form__error-box__error");
        span.innerHTML = error;

        errorsSection.appendChild(span);
      }
      return;
    }

    return true;

  };

  loadCards() {
    (async () => {

      const res = await fetch("./server/client.php?type=selectAll", {
        method: "GET",
        headers: {
          contentType: "application/json"
        }
      });
      const data = await res.json();

      this.sections.content.innerHTML = "";

      for (const client of data) {
        let card = this.buildCard(client.id, client.name, client.phone);
        this.sections.content.innerHTML += card;
      }

      this.delete();
      this.update();
      this.showCards();

    })();
  };

  delete() {
    let formsOfDelete = document.querySelectorAll("form[data-id=client__content__card__actions-box__delete-form]");

    for (let form of formsOfDelete) {

      form.onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        const action = async () => {
          await fetch("./server/client.php", {
            method: "POST",
            header: {
              contentType: "application/json"
            },
            body: formData
          });

          
          this.loadCards();
        };

        const message = "Deseja realmente excluir o cliente?";
        this.showDialogMessage(action, message);

      };
    };

  };

  update() {
    let formsOfUpdate = document.querySelectorAll("form[data-id=client__content__card__actions-box__update-form]");

    for (let form of formsOfUpdate) {

      form.onsubmit = (e) => {
        e.preventDefault();

        (async () => {
          const res = await fetch(`./server/client.php?type=selectById&id=${e.target.id.value}`, {
            method: "GET",
            header: {
              contentType: "application/json"
            }
          });

          const data = await res.json();

          let form = document.querySelector("#client__insert__form__form");
          form.name.value = data.name;
          form.phone.value = data.phone;
          form.id.value = data.id;

          this.showForm();

        })();

      };
    };
  };

  search() {

    let form = document.querySelector("#client__search__form__form");

    form.onsubmit = (e) => {
      e.preventDefault();

      (async () => {

        const res = await fetch(`./server/client.php?type=search&id=${e.target.id.value}&name=${e.target.name.value}&phone=${e.target.phone.value}`, {
          method: "GET",
          headers: {
            contentType: "application/json"
          }
        });
  
        const data = await res.json();
        
        this.sections.content.innerHTML = "";
        
        for (const client of data) {
          let card = this.buildCard(client.id, client.name, client.phone);
          this.sections.content.innerHTML += card;
        }
  
        this.delete();
        this.update();
        this.showCards();
  
      })();
    };
  };

}

export default Client;