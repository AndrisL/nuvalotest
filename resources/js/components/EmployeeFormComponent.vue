<template>    
    <div>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-primary" role="alert">
                    Please build a form where you can search each employee by name and get their workhours between selected dates.
                    Group workhours by month to show month by month how many hours they worked.
                </div>
            </div>
        </div>        
        <div class="row" v-if="errormessage !=''">
            <div class="col-12">
                <div class="alert alert-danger" role="alert">{{errormessage}}</div>
            </div>                
        </div>
        <div class="row">
            <div class="col-12">
                <form id="gethours" :action="formroute" v-on:submit="gethours" method="post">
                    <div class="form-group">                    
                        <select name="worker"  data-placeholder="Search employee" class="chosen-select" required>
                            <option value=""></option>
                            <option v-for="(name, index) in employeedata" :value="index">{{name}}</option>
                        </select>                    
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="start">Workhours date start</label>
                            <input id="datestart" name="datestart" class="form-control" type="text" autocomplete="off" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="end">Workhours date end</label>
                            <input id="dateend" name="dateend" class="form-control" type="text" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary mb-2" :disabled="disabledform">Confirm identity</button>                        
                    </div>       
                </form>  
            </div>
        </div>
        <div class="row">
            <div id="hours" class="col-12">
                <table class="table table-striped" v-if="is_refresh">
                    <thead>
                        <tr>
                            <th scope="col">Period</th>
                            <th scope="col">Hour</th>
                            <th scope="col">Minutes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(time, index) in times">
                            <td>{{index}}</td>
                            <td>{{time.hours}}</td>
                            <td>{{time.minutes}}</td>
                        </tr>
                    </tbody>                                        
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    $(function () {
        $('.chosen-select').chosen();
    });
    export default {
        props: [
            'employeedata',
            'formroute',
            'mindate',
            'maxdate'
        ],
        data() {
            return{
                times: [],
                errormessage: '',
                is_refresh: false,
                disabledform: false
            };
        },
        mounted() {
            $.datepicker.setDefaults({dateFormat: 'dd-mm-yy'});
            $('#datestart').datepicker({
                maxDate: this.maxdate
            });
            $('#dateend').datepicker({
                maxDate: this.maxdate
            });
            this.update();
        },
        methods: {
            update: function () {
            },
            gethours: function (event) {
                event.preventDefault();
                this.times = [];
                this.disabledform = true;
                this.errormessage = '';
                axios.post(this.formroute, $("#gethours").serialize()).then((response) => {
                    this.times = response.data;
                    this.is_refresh = true;
                    this.disabledform = false;
                }).catch((error) => {
                    this.is_refresh = false;
                    this.disabledform = false;
                    this.errormessage = error.message
                    console.log(error.message);
                });
            }
        }
    }
</script>