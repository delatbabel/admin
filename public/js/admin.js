(function($)
{
    var admin = function()
    {
        return this.init();
    };

    //setting up csrf token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': window.csrf
        }
    });

    admin.prototype = {

        //properties

        /*
         * Main admin container
         *
         * @type jQuery object
         */
        $container: null,

        /*
         * The container for the datatable
         *
         * @type jQuery object
         */
        $tableContainer: null,

        /*
         * The data table
         *
         * @type jQuery object
         */
        $dataTable: null,

        /*
         * The pixel points where the columns are hidden
         *
         * @type object
         */
        columnHidePoints: {},

        /*
         * If this is true, history.js has started
         *
         * @type bool
         */
        historyStarted: false,

        /*
         * Filters view model
         */
        filtersViewModel: {

            /* The filters for the current result set
             * array
             */
            filters: [],

            /* The options lists for any fields
             * object
             */
            listOptions: {},

            /**
             * The options for booleans
             * array
             */
            boolOptions: [{id: 'true', text: 'true'}, {id: 'false', text: 'false'}]
        },

        /*
         * KO viewModel
         */
        viewModel: {

            /*
             * KO data model
             */
            model: {},

            /*
             * If this is true, all the values have been initialized and we can
             *
             * bool
             */
            initialized: ko.observable(false),

            /* The model name for this data model
             * string
             */
            modelName: ko.observable(''),

            /* The model title for this data model
             * string
             */
            modelTitle: ko.observable(''),

            /* The title for single items of this model
             * string
             */
            modelSingle: ko.observable(''),

            /* The link (usually front-end) associated with this item
             * string
             */
            itemLink: ko.observable(null),

            /* The expand width of the edit area
             * int
             */
            expandWidth: ko.observable(null),

            /* The primary key value for this model
             * string
             */
            primaryKey: 'id',

            /* The number of rows per page
             * int
             */
            rowsPerPage: ko.observable(20),

            /* The columns for the current data model
             * array
             */
            columns: ko.observableArray(),

            /* The options lists for any fields
             * object
             */
            listOptions: {},

            /* The current pagination options
             * object
             */
            pagination: {
                page: ko.observable(),
                last: ko.observable(),
                total: ko.observable(),
                per_page: ko.observable(),
                isFirst: true,
                isLast: false,
            },

            /* The original edit fields array
             * array
             */
            originalEditFields: [],

            /* The original data when fetched from the server initially
             * object
             */
            originalData: {},

            /* The model edit fields
             * array
             */
            editFields: ko.observableArray(),

            /* The id of the active item. If it's null, there is no active item. If it's 0, the active item is new
             * mixed (null, int)
             */
            activeItem: ko.observable(null),

            /* The id of the last active item. This is set to null when an item is closed. 0 is new.
             * mixed (null, int)
             */
            lastItem: null,

            /* If this is set to true, the loading screen will be visible
             * bool
             */
            loadingItem: ko.observable(false),

            /* The id of the item currently being loaded
             * int
             */
            itemLoadingId: ko.observable(null),

            /* The id of the rows currently being loaded
             * int
             */
            rowLoadingId: 0,

            /* If this is set to true, the form becomes uneditable
             * bool
             */
            freezeForm: ko.observable(false),

            /* If this is set to true, the action buttons on the form cannot be accessed
             * bool
             */
            freezeActions: ko.observable(false),

            /* If this is set to true, the relationship constraints won't update
             * bool
             */
            freezeConstraints: false,

            /* The current constraints queue
             * object
             */
            constraintsQueue: {},

            /* If this is set to true, the relationship constraints queue won't process
             * bool
             */
            holdConstraintsQueue: true,

            /* If custom actions are supplied, they are stored here
             * array
             */
            actions: ko.observableArray(),

            /* If custom global actions are supplied, they are stored here
             * array
             */
            globalActions: ko.observableArray(),

            /* Holds the per-action permissions
             * object
             */
            actionPermissions: {},

            /* The languages array holds text for the current language
             * object
             */
            languages: {},

            /* The status message and the type ('', 'success', 'error')
             * strings
             */
            statusMessage: ko.observable(''),
            statusMessageType: ko.observable(''),

            /* The global status message and the type ('', 'success', 'error')
             * strings
             */
            globalStatusMessage: ko.observable(''),
            globalStatusMessageType: ko.observable(''),

            /**
             * Saves the item with the current settings. If id is 0, the server interprets it as a new item
             */
            saveItem: function()
            {
                var self = this,
                    saveData = ko.mapping.toJS(self);

                saveData._token = csrf;

                //if this is a new item, delete the primary key from the data array
                if (!saveData[self.primaryKey])
                    delete saveData[self.primaryKey];

                //iterate over the edit fields and ensure that the belongs_to relationships are false if they are an empty string
                $.each(self.editFields(), function(ind, field)
                {
                    if (field.relationship && !field.external && saveData[field.field_name] === '')
                    {
                        saveData[field.field_name] = false;
                    }
                });

                self.statusMessage(self.languages['saving']).statusMessageType('');
                self.freezeForm(true);

                $.ajax({
                    url: base_url +  self.modelName() + '/' + self[self.primaryKey]() + '/save',
                    data: saveData,
                    dataType: 'json',
                    type: 'POST',
                    complete: function()
                    {
                        self.freezeForm(false);
                        window.admin.resizePage();
                    },
                    success: function(response)
                    {
                        if (response.success) {
                            self.statusMessage(self.languages['saved']).statusMessageType('success');
                            self.updateSelfRelationships();
                            self.setData(response.data);

                            setTimeout(function()
                            {
                                window.admin.viewModel.closeItem();
                            }, 200);
                            // Reset DataTable
                            $("#customers").DataTable().ajax.reload(null, false);
                        }
                        else
                            self.statusMessage(response.errors).statusMessageType('error');
                    }
                });
            },

            /**
             * Deletes the active item
             */
            deleteItem: function()
            {
                var self = this,
                    conf = confirm(self.languages['delete_active_item']);

                if (!conf)
                    return false;

                self.statusMessage(self.languages['deleting']).statusMessageType('');
                self.freezeForm(true);

                $.ajax({
                    url: base_url + self.modelName() + '/' + self[self.primaryKey]() + '/delete',
                    data: {_token: csrf},
                    dataType: 'json',
                    type: 'POST',
                    complete: function()
                    {
                        window.admin.resizePage();
                    },
                    success: function(response)
                    {
                        if (response.success)
                        {
                            self.statusMessage(self.languages['deleted']).statusMessageType('success');
                            self.updateSelfRelationships();

                            setTimeout(function()
                            {
                                window.admin.viewModel.closeItem();
                            }, 500);
                            // Reset DataTable
                            $("#customers").DataTable().ajax.reload(null, false);
                        }
                        else
                            self.statusMessage(response.error).statusMessageType('error');
                    }
                });
            },

            /**
             * Callback for clicking an item
             */
            clickItem: function(id)
            {
                if (!this.loadingItem() && this.activeItem() !== id && this.actionPermissions.view)
                {
                    History.pushState({modelName: this.modelName(), id: id}, null, route + this.modelName() + '/' + id);
                }
            },

            /**
             * Gets the active item in the grid
             *
             * @param int   id
             */
            getItem: function(id)
            {
                var self = this;

                self.loadingItem(true);

                //override the edit fields to the original non-existent model
                adminData.edit_fields = self.originalEditFields;
                self.editFields(window.admin.prepareEditFields());

                //make sure constraints are only loaded once
                self.holdConstraintsQueue = true;

                //update all the info to the new item state
                ko.mapping.updateData(self, self.model, self.model);
                self.originalData = {};

                //scroll to the top of the page
                $('html, body').animate({scrollTop: 0}, 'fast')

                //if this is a new item (id is falsy), just overwrite the viewModel with the original data model
                if (!id)
                {
                    self.setUpNewItem();
                    return;
                }

                //freeze the relationship constraint updates
                self.freezeConstraints = true;

                self.itemLoadingId(id);

                $.ajax({
                    url: base_url + self.modelName() + '/' + id,
                    dataType: 'json',
                    success: function(data)
                    {
                        //if there was an error, kick out
                        if (data.success === false && data.errors)
                        {
                            alert(data.errors);
                            return;
                        }

                        if (self.itemLoadingId() !== id)
                        {
                            //if there are no currently-loading items, clear the form
                            if (self.itemLoadingId() === null)
                            {
                                self.loadingItem(false);
                                self.clearItem();
                            }
                        }
                        else
                            self.setData(data);
                    }
                });
            },

            /**
             * Sets the edit form up as a new item
             */
            setUpNewItem: function()
            {
                this.itemLoadingId(null);
                this.activeItem(0);

                //set the last item property which helps manage the animation states
                this.lastItem = 0;

                this.loadingItem(false);

                //run the constraints queue
                window.admin.runConstraintsQueue();
            },

            /**
             * Overrides the data in the view model
             *
             * @param object    data
             * @param
             */
            setData: function(data)
            {
                var self = this;

                //set the active item and update the model data
                self.activeItem(data[self.primaryKey]);
                self.loadingItem(false);

                //update the edit fields
                adminData.edit_fields = data.administrator_edit_fields;
                self.editFields(window.admin.prepareEditFields());

                //update the actions and the action permissions
                self.actions(data.administrator_actions);
                self.actionPermissions = data.administrator_action_permissions;

                //set the original values
                self.originalData = data;

                //set the new options for relationships
                $.each(adminData.edit_fields, function(ind, el)
                {
                    if (el.relationship && el.autocomplete)
                    {
                        self[el.field_name + '_autocomplete'] = data[el.field_name + '_autocomplete'];
                    }
                });

                //set the item link if it exists
                if (data.admin_item_link)
                {
                    self.itemLink(data.admin_item_link);
                }

                //set the last item property which helps manage the animation states
                self.lastItem = data[self.primaryKey];

                //fixes an error where the relationships wouldn't load
                setTimeout(function()
                {
                    //first clear the data
                    ko.mapping.updateData(self, self.model, self.model);

                    //then update the data
                    ko.mapping.updateData(self, self.model, data);

                    //unfreeze the relationship constraint updates
                    self.freezeConstraints = false;

                    window.admin.resizePage();

                    //run the constraints queue
                    window.admin.runConstraintsQueue();
                }, 50);
            },

            /**
             * Closes the item edit/create window
             */
            closeItem: function()
            {
                var $tableContainer = $('div.table_container');
                $tableContainer.css('margin-right', 0);
                History.pushState({modelName: this.modelName()}, null, route + this.modelName());

            },

            /**
             * Clears the current item
             */
            clearItem: function()
            {
                this.freezeForm(false);
                this.statusMessage('');
                this.statusMessageType('');
                this.itemLink(null);
                this.itemLoadingId(null);
                this.activeItem(null);
                this.lastItem = null;
            },

            /**
             * Opens the create item form
             */
            addNewItem: function()
            {
                //$('#users_list').resetSelection();
                this.getItem(0);
            },

            /**
             * Performs a custom action on an item or the whole model
             *
             * @param bool      isItem
             * @param string    action
             * @param object    messages
             * @param string    confirmation
             */
            customAction: function(isItem, action, messages, confirmation)
            {
                var self = this,
                    data = {_token: csrf, action_name: action},
                    url;

                //if a confirmation string was supplied, flash it in a confirm()
                if (confirmation)
                {
                    if (!confirm(confirmation))
                        return false;
                }

                //if this is an item action (compared to a global model action), set the proper url
                if (isItem)
                {
                    url = base_url + self.modelName() + '/' + self[self.primaryKey]() + '/custom_action';
                    self.statusMessage(messages.active).statusMessageType('');
                }
                //otherwise set the url and add the filters
                else
                {
                    url = base_url + self.modelName() + '/custom_action';
                    data.filters = self.getFilters();
                    data.page = self.pagination.page();
                    self.globalStatusMessage(messages.active).globalStatusMessageType('');
                }

                self.freezeForm(true);

                $.ajax({
                    url: url,
                    data: data,
                    dataType: 'json',
                    type: 'POST',
                    complete: function()
                    {
                        self.freezeForm(false);
                    },
                    success: function(response)
                    {
                        if (response.success)
                        {
                            if (isItem)
                            {
                                self.statusMessage(messages.success).statusMessageType('success');
                                self.setData(response.data);
                            }
                            else
                            {
                                self.globalStatusMessage(messages.success).globalStatusMessageType('success');
                            }

                            // if this is a redirect, redirect the user to the supplied url
                            if (response.redirect)
                                window.location.href = response.redirect;

                            //if there was a file download initiated, redirect the user to the file download address
                            if (response.download)
                                self.downloadFile(response.download);
                        }
                        else
                        {
                            if (isItem)
                                self.statusMessage(response.error).statusMessageType('error');
                            else
                                self.globalStatusMessage(response.error).globalStatusMessageType('error');
                        }
                    }
                });
            },

            /**
             * Initiates a file download
             *
             * @param string    url
             */
            downloadFile: function(url)
            {
                var hiddenIFrameId = 'hiddenDownloader',
                    iframe = document.getElementById(hiddenIFrameId);

                if (iframe === null)
                {
                    iframe = document.createElement('iframe');
                    iframe.id = hiddenIFrameId;
                    iframe.style.display = 'none';
                    document.body.appendChild(iframe);
                }

                iframe.src = url;
            },

            /**
             * Goes to the specified page
             *
             * @param string|int    page
             */
            page: function(page)
            {
                var currPage = parseInt(this.pagination.page()),
                    newPage = 1,
                    lastPage = parseInt(this.pagination.last());

                //if the value is 'prev' or 'next', increment or decrement
                if (page === 'prev')
                {
                    if (currPage > 1)
                    {
                        newPage = currPage - 1;
                    }
                }
                else if (page === 'next')
                {
                    if (currPage < lastPage)
                    {
                        newPage = currPage + 1;
                    }
                    else
                    {
                        newPage = lastPage;
                    }
                }
                else if (!isNaN(parseInt(page)))
                {
                    //set the page to the supplied value
                    if (page > lastPage)
                    {
                        newPage = lastPage;
                    }
                    else
                    {
                        newPage = page;
                    }
                }

                this.pagination.page(newPage);

            },

            /**
             * Gets a minimized filters array that can be sent to the server
             */
            getFilters: function()
            {
                var filters = [],
                    observables = ['value', 'min_value', 'max_value'];

                $.each(window.admin.filtersViewModel.filters, function(ind, el)
                {
                    var filter = {
                        field_name: el.field_name,
                        type: el.type,
                        value: el.value() ? el.value() : null,
                    };

                    //iterate over the observables to see if we should include them
                    $(observables).each(function(i, obs)
                    {
                        if (this in el)
                        {
                            filter[this] = el[this]() ? el[this]() : null;

                            if (obs === 'value' && filter[this] && el.type === 'belongs_to_many' && typeof filter[this] === 'string')
                            {
                                filter.value = filter.value.split(',');
                            }
                        }
                    });

                    //push this filter onto the filters array
                    filters.push(filter);
                });

                return filters;
            },

            /**
             * Determines if the provided field is dirty
             *
             * @param string
             *
             * @return bool
             */
            fieldIsDirty: function(field)
            {
                return this.originalData[field] != this[field]();
            },

            /**
             * Updates any self-relationships
             */
            updateSelfRelationships: function()
            {
                var self = this;

                //first we will iterate over the filters and update them if any exist
                $.each(window.admin.filtersViewModel.filters, function(ind, filter)
                {
                    var fieldIndex = ind,
                        fieldName = filter.field_name;

                    if ((!filter.constraints || !filter.constraints.length) && filter.self_relationship)
                    {
                        window.admin.filtersViewModel.filters[fieldIndex].loadingOptions(true);

                        $.ajax({
                            url: base_url + self.modelName() + '/update_options',
                            type: 'POST',
                            dataType: 'json',
                            data: {fields: [{
                                type: 'filter',
                                field: fieldName,
                                selectedItems: filter.value()
                            }]},
                            complete: function()
                            {
                                window.admin.filtersViewModel.filters[fieldIndex].loadingOptions(false);
                            },
                            success: function(response)
                            {
                                //update the options
                                window.admin.filtersViewModel.listOptions[fieldName](response[fieldName]);
                            }
                        });

                    }
                });

                //then we'll update the edit fields
                $.each(self.editFields(), function(ind, field)
                {
                    var fieldName = field.field_name;

                    //if there are no constraints for this field and if it is a self-relationship, update the options
                    if ((!field.constraints || !field.constraints.length) && field.self_relationship)
                    {
                        field.loadingOptions(true);

                        $.ajax({
                            url: base_url + self.modelName() + '/update_options',
                            type: 'POST',
                            dataType: 'json',
                            data: {fields: [{
                                type: 'edit',
                                field: fieldName,
                                selectedItems: self[fieldName]()
                            }]},
                            complete: function()
                            {
                                field.loadingOptions(false);
                            },
                            success: function(response)
                            {
                                //update the options
                                self.listOptions[fieldName] = response[fieldName];
                            }
                        });

                    }
                });
            }
        },



        //methods

        /**
         * Init method
         */
        init: function()
        {
            var self = this;

            //set up the basic pieces of data
            this.viewModel.model = adminData.data_model;
            this.$container = $('#admin_content');

            var viewModel = ko.mapping.fromJS(this.viewModel.model);

            $.extend(this.viewModel, viewModel);

            this.viewModel.columns(this.prepareColumns());
            this.viewModel.modelName(adminData.model_name);
            this.viewModel.modelTitle(adminData.model_title);
            this.viewModel.modelSingle(adminData.model_single);
            this.viewModel.expandWidth(adminData.expand_width);
            this.viewModel.rowsPerPage(adminData.rows_per_page);
            this.viewModel.primaryKey = adminData.primary_key;
            this.viewModel.actions(adminData.actions);
            this.viewModel.globalActions(adminData.global_actions);
            this.viewModel.actionPermissions = adminData.action_permissions;
            this.viewModel.languages = adminData.languages;

            //now that we have most of our data, we can set up the computed values
            this.initComputed();

            //prepare the filters
            this.filtersViewModel.filters = this.prepareFilters();

            //prepare the edit fields
            this.viewModel.originalEditFields = adminData.edit_fields;
            this.viewModel.editFields(this.prepareEditFields());

            //set up the relationships
            this.initRelationships();

            //set up the KO bindings
            ko.applyBindings(this.viewModel, $('#content')[0]);
            ko.applyBindings(this.filtersViewModel, $('#filters_sidebar_section')[0]);

            //set up pushstate history
            this.initHistory();

            //set up the subscriptions
            this.initSubscriptions();

            //set up the events
            this.initEvents();

            //run an initial page resize
            this.resizePage();

            // Not sure if this should go here or not. Set up the markitup region
            $(".markitup").markItUp(mySettings);

            //finally run a timer to overcome bugs with select2
            setTimeout(function()
            {
                self.viewModel.initialized(true);
            }, 1000);

            return this;
        },

        /**
         * Prepare the filters
         *
         * @return array with value observables
         */
        prepareFilters: function()
        {
            var filters = [];

            $.each(adminData.filters, function(ind, filter)
            {
                var observables = ['value', 'min_value', 'max_value'];

                //iterate over the desired observables and check if they're there. if so, assign them an observable slot
                $.each(observables, function(i, obs)
                {
                    if (obs in filter)
                    {
                        filter[obs] = ko.observable(filter[obs]);
                    }
                });

                //if this is a relationship field, we want to set up the loading options observable
                if (filter.relationship)
                {
                    filter.loadingOptions = ko.observable(false);
                }

                filter.field_id = 'filter_field_' + filter.field_name;

                filters.push(filter);
            });

            return filters;
        },

        /**
         * Prepare the edit fields
         *
         * @return object with loadingOptions observables
         */
        prepareEditFields: function()
        {
            var self = this,
                fields = [];

            $.each(adminData.edit_fields, function(ind, field)
            {
                //if this is a relationship field, set up the loadingOptions observable
                if (field.relationship)
                {
                    field.loadingOptions = ko.observable(false);
                    field.constraintLoading = ko.observable(false);
                }

                //if this is an image field, set the upload params
                if (field.type === 'image' || field.type === 'file')
                {
                    field.uploading = ko.observable(false);
                    field.upload_percentage = ko.observable(0);
                }

                //add the id field
                field.field_id = 'edit_field_' + ind;

                fields.push(field);
            });

            return fields;
        },

        /**
         * Sets up the column model with various observable values
         *
         * @return array
         */
        prepareColumns: function()
        {
            var self = this,
                columns = [];

            $.each(adminData.column_model, function(ind, column)
            {
                column.visible = ko.observable(column.visible);
                columns.push(column);
            });

            return columns;
        },

        /**
         * Set up the relationship items
         */
        initRelationships: function()
        {
            var self = this;

            //set up the filters
            $.each(adminData.filters, function(ind, el)
            {
                if (el.relationship)
                    self.filtersViewModel.listOptions[ind] = ko.observableArray(el.options);
            });

            //set up the edit fields
            $.each(adminData.edit_fields, function(ind, el)
            {
                if (el.relationship)
                    self.viewModel.listOptions[ind] = el.options;

                // add any loaded option to the autocomplete array
                if (el.autocomplete)
                {
                    if(! (el.field_name + '_autocomplete' in self.viewModel) )
                        self.viewModel[el.field_name + '_autocomplete'] = [];
                    $.each(el.options, function(x, option)
                    {
                        self.viewModel[el.field_name + '_autocomplete'][option.id] = option;
                    });
                }
            });
        },

        /**
         * Inits the KO subscriptions
         */
        initSubscriptions: function()
        {
            var self = this,
                runFilter = function(val)
                { };

            //iterate over filters
            $.each(self.filtersViewModel.filters, function(ind, filter)
            {
                //subscribe to the value field
                self.filtersViewModel.filters[ind].value.subscribe(function(val)
                {
                    //if this is an id field, make sure it's an integer
                    if (self.filtersViewModel.filters[ind].type === 'key')
                    {
                        var intVal = isNaN(parseInt(val)) ? '' : parseInt(val);

                        self.filtersViewModel.filters[ind].value(intVal);
                    }

                });

                //check if there's a min and max value. if so, subscribe to those as well
                if ('min_value' in filter)
                {
                    self.filtersViewModel.filters[ind].min_value.subscribe(runFilter);
                }
                if ('max_value' in filter)
                {
                    self.filtersViewModel.filters[ind].max_value.subscribe(runFilter);
                }
            });

            //iterate over the edit fields
            $.each(self.viewModel.editFields(), function(ind, field)
            {
                //if there are constraints to maintain, set up the subscriptions
                if (field.constraints && self.getObjectSize(field.constraints))
                {
                    self.establishFieldConstraints(field);
                }
            });

            //subscribe to page change
            self.viewModel.pagination.page.subscribe(function(val)
            {
                self.viewModel.page(val);
            });
        },

        /**
         * Establish constraints
         *
         * @param object    field
         */
        establishFieldConstraints: function(field)
        {
            var self = this;

            //we want to subscribe to changes on the OTHER fields since that's what defines changes to this one
            $.each(field.constraints, function(key, relationshipName)
            {
                var fieldName = field.field_name,
                    f = field,
                    constraintsLength = self.getFieldConstraintsLength(key);

                self.viewModel[key].subscribe(function(val)
                {
                    if (self.viewModel.freezeConstraints || f.loadingOptions())
                        return;

                    //if this key hasn't been set up yet, set it
                    if (!self.viewModel.constraintsQueue[key])
                        self.viewModel.constraintsQueue[key] = {};

                    //add the constraint to the queue
                    self.viewModel.constraintsQueue[key][fieldName] = f;

                    var currentQueueLength = Object.keys(self.viewModel.constraintsQueue[key]).length;

                    if (!self.viewModel.holdConstraintsQueue && (currentQueueLength === constraintsLength))
                        self.runConstraintsQueue();
                });
            });
        },

        /**
         * Sets the constrainer's constraintLoading field to true
         *
         * @param string    key
         *
         * @return int
         */
        getFieldConstraintsLength: function(key)
        {
            var length = 0;

            //iterate over the edit fields until we find our match
            $.each(this.viewModel.editFields(), function(ind, field)
            {
                if (field.constraints && field.constraints[key])
                {
                    length++;
                }
            });

            return length;
        },

        /**
         * Sets the constrainer's constraintLoading field to true
         *
         * @param string    key
         * @param bool      freeze
         */
        setConstrainerFreeze: function(key, freeze)
        {
            //iterate over the edit fields until we find our match
            $.each(this.viewModel.editFields(), function(ind, field)
            {
                if (field.field_name === key)
                {
                    field.constraintLoading(freeze);
                    return false;
                }
            });
        },

        /**
         * Sets a field's loadingOptions
         *
         * @param string    fieldName
         * @param bool      type
         */
        setFieldLoadingOptions: function(fieldName, type)
        {
            //iterate over the edit fields until we find our match
            $.each(this.viewModel.editFields(), function(ind, field)
            {
                if (field.field_name === fieldName)
                {
                    field.loadingOptions(type);
                    return false;
                }
            });
        },

        /**
         * Runs the constraints queue
         */
        runConstraintsQueue: function()
        {
            var self = this,
                fields = self.buildConstraintsFromQueue();

            //if there are no fields, exit out
            if (!fields.length)
                return;

            //freeze the actions
            self.viewModel.freezeActions(true);

            $.ajax({
                url: base_url + self.viewModel.modelName() + '/update_options',
                type: 'POST',
                dataType: 'json',
                data: {
                    fields: fields
                },
                complete: function()
                {
                    self.viewModel.freezeActions(false);

                    $.each(self.viewModel.constraintsQueue, function(key, fieldConstraints)
                    {
                        $.each(fieldConstraints, function(fieldName, field)
                        {
                            self.setFieldLoadingOptions(fieldName, false);
                            self.setConstrainerFreeze(key, false);
                        });
                    });

                    //clear the constraints queue
                    self.viewModel.constraintsQueue = {};
                    self.viewModel.holdConstraintsQueue = false;
                },
                success: function(response)
                {
                    //iterate over the results and put them in the autocomplete array
                    $.each(response, function(fieldName, el)
                    {
                        var data = {};

                        $.each(el, function(i, e)
                        {
                            data[e.id] = e;
                        });

                        self.viewModel[fieldName + '_autocomplete'] = data;

                        //update the options
                        self.viewModel.listOptions[fieldName] = el;
                    });
                }
            });
        },

        /**
         * Prepares the constraints for the queue job
         */
        buildConstraintsFromQueue: function()
        {
            var self = this,
                allConstraints = [];

            $.each(self.viewModel.constraintsQueue, function(key, fieldConstraints)
            {
                $.each(fieldConstraints, function(fieldName, field)
                {
                    var constraints = {};

                    //set the field to loading and freeze the constrainer
                    self.setFieldLoadingOptions(fieldName, true);
                    self.setConstrainerFreeze(key, true);

                    //iterate over this field's constraints
                    $.each(field.constraints, function(key, relationshipName)
                    {
                        constraints[key] = self.viewModel[key]();
                    });

                    allConstraints.push({
                        constraints: constraints,
                        type: 'edit',
                        field: fieldName,
                        selectedItems: self.viewModel[fieldName]()
                    });
                });
            });

            return allConstraints;
        },

        /**
         * Inits the page events
         */
        initEvents: function()
        {
            var self = this;

            //clicking the new item button
            $('#content').on('click', 'div.results_header a.new_item', function(e)
            {
                e.preventDefault();
                History.pushState({modelName: self.viewModel.modelName(), id: 0}, null, route + self.viewModel.modelName() + '/new');
            });

            //clicking the edit item button
            $('#content').on('click', 'div.results_header a.edit_item', function(e)
            {
                e.preventDefault();
                var tmpID = $($(this)[0]).attr('data-id');
                History.pushState({modelName: self.viewModel.modelName(), id: tmpID}, null, route + self.viewModel.modelName() + '/' + tmpID);
            });

            //resizing the window
            $(window).resize(self.resizePage);

            //mousedowning or keypressing anywhere should resize the page as well
            $('body').on('mouseup keypress', self.resizePage);

            //set up the history event callback
            History.Adapter.bind(window,'statechange',function() {
                var state = History.getState();

                //if the ignore key is true, or if this is the inital state, exit out.
                if (state.data.ignore || (state.data.init && !self.historyStarted))
                    return;


                //if the model name is present
                if ('modelName' in state.data)
                //if that model name isn't the current model name, we are updating the model
                    if (state.data.modelName !== self.viewModel.modelName())
                    //get the new model
                        self.viewModel.getNewModel(state.data);

                //if the state data has an id field and if it's not the active item
                if ('id' in state.data)
                {
                    //get the new item (this includes when state.data.id === 0, which means it should be a new item)
                    if (state.data.id !== self.viewModel.activeItem())
                        self.viewModel.getItem(state.data.id);
                }
                else
                {
                    //otherwise, assume that the user wants to be taken back to the results page. close the form
                    self.viewModel.clearItem();
                }
            });
        },

        /**
         * Sets up the push state's initial state
         */
        initHistory: function()
        {
            var historyData = {
                    modelName: this.viewModel.modelName(),
                    init: true
                },
                uri = route + this.viewModel.modelName();

            //if the admin data had an id supplied, it means this is either the edit page or the new item page
            if ('id' in adminData)
            {
                //if the view model hasn't been set up yet, wait for it to be set up
                var timer = setInterval(function()
                {
                    if (window.admin)
                    {
                        window.admin.viewModel.getItem(adminData.id);
                        historyData.id = adminData.id;
                        uri += '/' + (historyData.id ? historyData.id : 'new');

                        //now call the same to trigger the statechange event
                        History.pushState(historyData, null, uri);

                        clearInterval(timer);
                    }
                }, 100);
            }

            this.historyStarted = true;
        },

        /**
         * Initializes the computed observables
         */
        initComputed: function()
        {
            //pagination information
            this.viewModel.pagination.isFirst = ko.computed(function()
            {
                return this.pagination.page() == 1;
            }, this.viewModel);

            this.viewModel.pagination.isLast = ko.computed(function()
            {
                return this.pagination.page() == this.pagination.last();
            }, this.viewModel);

        },

        /**
         * Helper to get an object's size
         *
         * @param object
         *
         * @return int
         */
        getObjectSize: function(obj)
        {
            var size = 0, key;

            for (key in obj)
            {
                if (obj.hasOwnProperty(key)) size++;
            }

            return size;
        },

        /**
         * Handles a window resize to make sure the admin area is always
         */
        resizePage: function()
        {
            setTimeout(function()
            {
                var winHeight = $(window).height(),
                    itemEditHeight = $('div.item_edit').outerHeight() + 200,
                    usedHeight = winHeight > itemEditHeight ? winHeight : itemEditHeight,
                    size = window.getComputedStyle(document.body, ':after').getPropertyValue('content');

                //resize the page height
                $('#admin_page').css({minHeight: usedHeight});

            }, 50);
        }
    };


    //set up the admin instance
    $(function() {
        if ($('#admin_page').length)
            window.admin = new admin();
    });
})(jQuery);
