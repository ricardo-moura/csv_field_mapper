<template>
    <div class="container">
        <header class="blog-header py-3">
            <div class="row">
                <div class="col-4"></div>
                <div class="col-4 text-center">
                    <h1>CSV Importer</h1>
                </div>
                <div class="col-4"></div>
            </div>
        </header>
        <main role="main" v-if="success === ''">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" @click="reloadPage()">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">File Upload</li>
                </ol>
            </nav>
            <form @submit="formSubmit" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group border-1 p-3">
                            <label for="exampleFormControlFile1">Please select your csv file:</label>
                            <input type="file" class="form-control-file" v-on:change="onFileChange">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <button class="btn btn-primary">Next</button>
                    </div>
                </div>
            </form>
        </main>
        <main role="main" v-if="success == '200'">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" @click="reloadPage()">Home</a></li>
                    <li class="breadcrumb-item" aria-current="page">File Upload</li>
                    <li class="breadcrumb-item active" aria-current="page">Review</li>
                </ol>
            </nav>
            <form @submit="importData">
                <input type="hidden" v-bind:value="csv_id" />
                <div v-if="!custom_headers.length">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning text-center" role="alert">
                                Your .csv file has the same columns as the target system!</br>
                                <b>For this reason, it will not be necessary to do a manual mapping!</b>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <button class="btn btn-primary">Start importation</button>
                        </div>
                    </div>
                </div>
                <div v-else-if="custom_headers">
                    <div class="row mb-3">
                        <div class="col-md-12">

                            <div class="card">
                                <div class="card-header">
                                    Before start
                                </div>
                                <div class="card-body">
                                    <p class="card-text">This application provides some default columns and you can use one name per column mapped.</p>
                                    <p class="card-text">Default columns name:</p>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Required</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-danger font-weight-bold">
                                                <td>team_id</td>
                                                <td>Team id number</td>
                                                <td>Yes</td>
                                            </tr>
                                            <tr class="text-danger font-weight-bold">
                                                <td>phone</td>
                                                <td>Phone number</td>
                                                <td>Yes</td>
                                            </tr>
                                            <tr>
                                                <td>name</td>
                                                <td>Person name</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>email</td>
                                                <td>Email address</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>sticky_phone_number_id</td>
                                                <td>Sticky phone number</td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="alert alert-warning text-center" role="alert">
                                        <b>Please don't use the same column name twice!</b>
                                    </div>
                                    <div class="alert alert-warning text-center" role="alert">
                                        <b>If your file has any of these columns: team_id, phone, name, email,sticky_phone_number_id, they have already been mapped automatically!</b>
                                    </div>
                                    <p class="card-text">If you don't want to use the columns above, you can create your own custom column.</p>
                                    <p class="card-text">Your custom column should be typed without space, eg:</p>
                                    <ul>
                                        <li>instagram_profile</li>
                                        <li>twitter_profile</li>
                                        <li>web_site_address</li>
                                        <li>github_link</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Map the colums</h3>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>CSV Column</th>
                                            <th>Map column to</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) of custom_headers">
                                            <td>{{item}}</td>
                                            <td>
                                                <input type="text" v-model="form[item]" required="required" class="form-control col-4"/>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <button class="btn btn-primary">Start importation</button>
                        </div>
                    </div>
                </div>

            </form>
        </main>
        <main role="main" v-if="success == '201'">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" @click="reloadPage()">Home</a></li>
                    <li class="breadcrumb-item" aria-current="page">File Upload</li>
                    <li class="breadcrumb-item" aria-current="page">Review</li>
                    <li class="breadcrumb-item active" aria-current="page">Finished importation</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col-md-12">
                    <h2>Congratulations</h2>
                    <div class="alert alert-success" role="alert">
                        Your importation was finished successfully!
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button class="btn btn-primary" @click="reloadPage()">Create a new importation</button>
                </div>
            </div>
        </main>
    </div>
</template>
<script>
export default {
    data() {
        return {
            file: '',
            success: '',
            headers: [],
            custom_headers: [],
            form: {},
            csv_id: ''
        };
    },
    methods: {
        reloadPage() {
            window.location.reload();
        },
        importData(e) {
            e.preventDefault();
            console.log('chegou aqui');
                const formData = new FormData()
                let currentObj = this;
                Object.keys(this.form).forEach(e => {
                    formData.append(e, this.form[e]);
                })
                formData.append('csv_id', this.csv_id);
                axios.post('/api/importation/start', formData)
                .then(function (response) {
                    currentObj.success = response.status;
                })
                .catch(function (error) {
                    currentObj.output = error;
                });
        },
        noUnderscore(str) {
            let i, frags = str.split('_');
            for (i=0; i<frags.length; i++) {
                frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
            }
            return frags.join(' ');
        },
        onFileChange(e){
            this.file = e.target.files[0];
        },
        formSubmit(e) {
            e.preventDefault();
            let currentObj = this;

            const config = {
                headers: { 'content-type': 'multipart/form-data' }
            }

            let formData = new FormData();
            formData.append('csv_file', this.file);

            axios.post('/api/importation/file-upload', formData)
            .then( response => {
                if (response.status !== 200) {
                    console.log('diferent response status');
                    console.log(response);
                }
                currentObj.success = response.status;
                currentObj.headers = response.data.csv_headers;
                currentObj.custom_headers = response.data.csv_custom_headers;
                currentObj.csv_data = response.data.csv_data;
                currentObj.csv_id = response.data.csv_id;
            })
            .catch(error => {
                console.log(error);
            })
        }
    }
}
</script>
