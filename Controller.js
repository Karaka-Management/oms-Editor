import { jsOMS }      from '../../jsOMS/Utils/oLib.js';
import { Autoloader } from '../../jsOMS/Autoloader.js';
import { Editor } from './Models/Editor.js';

Autoloader.defineNamespace('omsApp.Modules');

/**
 * @feature Create immediate text preview similar to a rich text editor or Typora
 *      https://github.com/Karaka-Management/oms-Editor/issues/4
 */
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