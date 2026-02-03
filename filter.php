<!DOCTYPE html>
<html>
<head>
<style>
	table{
		border-collapse: collapse;
		text-align: center;
	}
	th,td{
	padding: 8px;
	border: 1px solid black;
	}
	thead{
	background-color: chocolate;
	color: aliceblue
	}
	#gender
	{
	background: linear-gradient(0.25turn, #3f87a6, #ebf8e1, #f69d3c);
	border-radius: 15px;
	font-size: large;
	text-align: center;
	}
</style>
</head>
<body>
	<center>
		<h1>Filter The Student Table With DropdownList</h1>
		<h2>Using Javascript & CSS</h2>
		<hr/>
		<label for="gender">Filter By Gender :</label>
		<select id="gender" onchange="filterTable()">
			<option value="all">all</option>
			<option value="Male">Male</option>
			<option value="Female">Female</option>
		</select>
		<hr/>
		<table id="student-table">
			<thead>
				<tr>
					<th>Student Name</th>
					<th>Gender</th>
					<th>Age</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Charan</td>
					<td>Male</td>
					<td>32</td>
				</tr>
				<tr>
					<td>James</td>
					<td>Male</td>
					<td>35</td>
				</tr>
				<tr>
					<td>Lalitha</td>
					<td>Female</td>
					<td>30</td>
				</tr>
				<tr>
					<td>Rani</td>
					<td>Female</td>
					<td>20</td>
				</tr>
				<tr>
					<td>James</td>
					<td>Male</td>
					<td>34</td>
				</tr>
			</tbody>
		</table>
	</center>
	<script>
		function filterTable(){
			var dropdown=document.getElementById("gender");
			var selectedValue=dropdown.value;
			var table=document.getElementById('student-table');
			var rows=table.getElementsByTagName("tr");

			for(var i=1;i<rows.length;i++)
			{
				var row=rows[i];
				var gender=row.cells[1].textContent.trim();

				if(selectedValue==="all" || gender===selectedValue)
				{
					row.style.display="";
				}
				else
				{
					row.style.display="none";
			}
		}
			}
	</script>
</body>
</html>
