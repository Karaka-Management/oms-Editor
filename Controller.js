import { jsOMS }      from '../../jsOMS/Utils/oLib.js';
import { Autoloader } from '../../jsOMS/Autoloader.js';
import { Editor } from './Models/Editor.js';

Autoloader.defineNamespace('omsApp.Modules');

omsApp.Modules.Editor = class {
    constructor(app)
    {
        this.app     = app;
        this.editors = {};
    };

    bind (id)
    {
        const e    = typeof id === 'undefined' ? document.getElementsByClassName('m-editor') : [id],
            length = e.length;

        for (let i = 0; i < length; ++i) {
            this.bindElement(e[i].id);
        }
    };

    bindElement (id)
    {
        this.editors[id] = new Editor(id);
        this.editors[id].bind();
    };
};

window.omsApp.moduleManager.get('Editor').bind();